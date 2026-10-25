<?php

namespace App\Http\Controllers;

use App\Enums\LoteEstado;
use App\Models\Lote;
use App\Models\Muestreo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MuestreoController extends Controller
{
    public function index(Request $request): View
    {
        $muestreos = Muestreo::query()->forCurrentCompany()
            ->with(['lote.productor', 'inspector'])->withCount('inspecciones')
            ->when($request->filled('buscar'), fn ($query) => $query->whereHas('lote',
                fn ($lote) => $lote->where('codigo_lote', 'ilike', '%'.trim((string) $request->input('buscar')).'%')))
            ->latest('id_muestreo')->paginate(12)->withQueryString();

        return view('muestreos.index', compact('muestreos'));
    }

    public function create(Request $request): View
    {
        $lotes = Lote::query()->forCurrentCompany()->with(['productor', 'recepcion'])
            ->whereHas('recepcion')
            ->where('estado', 'RECIBIDO')->whereDoesntHave('muestreos')
            ->orderByDesc('id_lote')->get();
        $loteSeleccionado = $request->integer('lote');

        $procedures = \App\Models\SamplingProcedure::forCurrentCompany()->where('activo', true)->orderBy('nombre')->get();
        $fixedLot = $request->filled('lote') ? $lotes->firstWhere('id_lote', $loteSeleccionado) : null;
        abort_if($request->filled('lote') && !$fixedLot, 404, 'El lote no está disponible para muestreo.');
        return view('muestreos.create', compact('lotes', 'loteSeleccionado', 'procedures', 'fixedLot'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->rol?->codigo === 'INSPECTOR', 403);
        $empresaId = (int) $request->user()->id_empresa;
        $request->validate(['procedure_id' => ['nullable', 'integer', 'prohibited_if:sampling_mode,manual'], 'sampling_mode' => ['sometimes', Rule::in(['authorized', 'manual'])], 'id_lote' => ['required', 'integer', Rule::exists('lote', 'id_lote')->where('id_empresa', $empresaId)]]);
        $procedure = null;
        if ($request->filled('procedure_id')) {
            $request->validate(['procedure_id' => ['integer', 'min:1']]);
            $procedure = \App\Models\SamplingProcedure::forCurrentCompany()->where('activo', true)->findOrFail($request->integer('procedure_id'));
            $request->merge(['protocolo' => $procedure->nombre.' · '.$procedure->version, 'metodo' => $procedure->metodo,
                'criterio_muestra' => $procedure->criterio_muestra, 'seleccion_frutos' => $procedure->seleccion_frutos]);
        }
        $manual = !$procedure;
        abort_if($manual && !config('operations.manual_sampling_enabled'), 403, 'Tu empresa debe configurar un procedimiento autorizado antes de registrar el muestreo.');
        if ($request->input('sampling_mode') === 'authorized' && !$procedure) throw \Illuminate\Validation\ValidationException::withMessages(['procedure_id' => 'Selecciona un procedimiento autorizado o solicita una excepción manual.']);
        $data = $request->validate([
            'id_lote' => ['required', 'integer', Rule::exists('lote', 'id_lote')->where('id_empresa', $empresaId)],
            'tamano_muestra' => ['required', 'integer', 'min:1', 'max:'.($procedure?->max_frutos ?? config('operations.sampling_max_fruits'))],
            'metodo' => ['required', 'string', 'max:80'],
            'observaciones' => ['nullable', 'string', 'max:3000'],
            'protocolo' => ['required', 'string', 'max:200'],
            'criterio_muestra' => ['required', 'string', 'max:1500'],
            'seleccion_frutos' => ['required', 'string', 'max:1500'],
            'motivo_desviacion' => [Rule::requiredIf(fn () => $procedure?->tamano_recomendado && $request->integer('tamano_muestra') !== $procedure->tamano_recomendado), 'nullable', 'string', 'max:1500'],
            'motivo_excepcion' => [Rule::requiredIf(fn () => $manual && $request->has('sampling_mode')), 'nullable', 'string', 'max:1500'],
            'confirmar_instrucciones' => ['sometimes', 'accepted'],
            'fuente_poblacion' => [Rule::requiredIf(fn () => $request->has('sampling_mode') && $request->filled('poblacion_estimada')), 'nullable', 'string', 'max:500'],
            'confirmar_carga' => ['sometimes', 'accepted'],
            'poblacion_estimada' => [Rule::requiredIf(fn () => $procedure?->requiere_poblacion), 'nullable', 'integer', 'min:1', 'max:100000000'],
        ]);

        if ($procedure && $request->has('sampling_mode') && !$request->boolean('confirmar_instrucciones')) throw \Illuminate\Validation\ValidationException::withMessages(['confirmar_instrucciones' => 'Confirma que seguirás las instrucciones del procedimiento.']);
        unset($data['confirmar_instrucciones']);
        if ((int) $data['tamano_muestra'] > config('operations.sampling_warning_fruits') && !$request->boolean('confirmar_carga')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['confirmar_carga' => 'Esta muestra requiere identificar y revisar una cantidad elevada de frutos. Confirma la carga de trabajo antes de continuar.']);
        }
        unset($data['confirmar_carga']);
        if (!empty($data['poblacion_estimada']) && (int) $data['tamano_muestra'] > (int) $data['poblacion_estimada']) throw \Illuminate\Validation\ValidationException::withMessages(['tamano_muestra' => 'La muestra no puede superar la población estimada de frutos.']);
        $sampling = \Illuminate\Support\Arr::only($data, ['protocolo', 'criterio_muestra', 'seleccion_frutos', 'poblacion_estimada', 'motivo_desviacion', 'motivo_excepcion', 'fuente_poblacion']);
        $sampling += ['modalidad' => $manual ? 'MANUAL' : 'AUTORIZADO'];
        if ($manual) $sampling['motivo_excepcion'] = $data['motivo_excepcion'] ?? $data['criterio_muestra'];
        if ($procedure) $sampling += ['procedure_id' => $procedure->id, 'max_frutos' => $procedure->max_frutos, 'tamano_recomendado' => $procedure->tamano_recomendado];
        $data = \Illuminate\Support\Arr::except($data, ['protocolo', 'criterio_muestra', 'seleccion_frutos', 'poblacion_estimada', 'motivo_desviacion', 'motivo_excepcion', 'fuente_poblacion']);
        $data = app(\App\Services\MaestroCatalog::class)->validate($data, ['metodo' => 'metodo'], $empresaId);
        $muestreo = DB::transaction(function () use ($data, $empresaId, $request, $sampling, $procedure) {
            $lote = Lote::query()->forCurrentCompany()->lockForUpdate()->findOrFail($data['id_lote']);
            abort_if($procedure && $procedure->producto && $procedure->producto !== $lote->producto, 422, 'El procedimiento seleccionado corresponde a otro producto.');
            abort_if(!$lote->recepcion()->exists(), 422, 'Confirma la recepción del lote antes de crear el muestreo.');
            abort_if($lote->muestreos()->exists() || $lote->estado->value !== 'RECIBIDO', 422, 'Este lote ya inició su muestreo. Las reinspecciones se solicitan desde el dictamen.');
            $muestreo = Muestreo::create([
                ...$data,
                'id_empresa' => $empresaId,
                'id_inspector' => $request->user()->id_usuario,
                'estado' => 'REGISTRADO',
            ]);
            app(\App\Observers\OperationalObserver::class)->record($muestreo, 'PROTOCOLO_MUESTREO', $sampling);
            // PostgreSQL genera la serie en el servidor: una inserción, sin 200 viajes
            // a Neon ni mantener 100 000 modelos/filas en la memoria de PHP.
            DB::insert(
                "INSERT INTO frutos_muestra (id_empresa, id_muestreo, numero, codigo, created_at, updated_at)
                 SELECT ?, ?, numero, ? || '-F' || LPAD(numero::text, GREATEST(5, LENGTH(numero::text)), '0'), ?, ?
                 FROM generate_series(1, CAST(? AS integer)) AS frutos(numero)",
                [$empresaId, $muestreo->id_muestreo, 'M'.$muestreo->id_muestreo, now(), now(), $data['tamano_muestra']]
            );
            if ($lote->estado === LoteEstado::RECIBIDO) {
                $lote->update(['estado' => LoteEstado::EN_MUESTREO]);
            }
            return $muestreo;
        });

        return redirect()->route('muestreos.show', $muestreo->id_muestreo)
            ->with('success', 'Muestreo registrado. Ahora puedes iniciar la inspección.');
    }

    public function show(int $muestreo): View
    {
        $muestreo = Muestreo::query()->forCurrentCompany()
            ->with(['lote.productor', 'inspector', 'inspecciones.inspector', 'inspecciones.dictamen'])
            ->findOrFail($muestreo);

        $frutos = $muestreo->frutos()->withCount('evidencias')->orderBy('numero')->paginate(30);
        $evento = DB::table('eventos_trazabilidad')->where('id_empresa', $muestreo->id_empresa)->where('entidad', 'Muestreo')->where('registro', $muestreo->id_muestreo)->where('evento', 'PROTOCOLO_MUESTREO')->latest('id')->first();
        $protocolo = $evento ? json_decode($evento->cambios, true) : [];
        return view('muestreos.show', compact('muestreo', 'frutos', 'protocolo'));
    }
}
