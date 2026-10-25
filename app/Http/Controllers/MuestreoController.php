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
        return view('muestreos.create', compact('lotes', 'loteSeleccionado', 'procedures'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->rol?->codigo === 'INSPECTOR', 403);
        $empresaId = (int) $request->user()->id_empresa;
        $procedure = null;
        if ($request->filled('procedure_id')) {
            $request->validate(['procedure_id' => ['integer', 'min:1']]);
            $procedure = \App\Models\SamplingProcedure::forCurrentCompany()->where('activo', true)->findOrFail($request->integer('procedure_id'));
            $request->merge(['protocolo' => $procedure->nombre.' · '.$procedure->version, 'metodo' => $procedure->metodo,
                'criterio_muestra' => $procedure->criterio_muestra, 'seleccion_frutos' => $procedure->seleccion_frutos]);
        }
        $data = $request->validate([
            'id_lote' => ['required', 'integer', Rule::exists('lote', 'id_lote')->where('id_empresa', $empresaId)],
            'tamano_muestra' => ['required', 'integer', 'min:1', 'max:'.($procedure?->max_frutos ?? config('operations.sampling_max_fruits'))],
            'metodo' => ['required', 'string', 'max:80'],
            'observaciones' => ['nullable', 'string', 'max:3000'],
            'protocolo' => ['required', 'string', 'max:200'],
            'criterio_muestra' => ['required', 'string', 'max:1500'],
            'seleccion_frutos' => ['required', 'string', 'max:1500'],
            'confirmar_carga' => ['nullable', 'accepted'],
            'poblacion_estimada' => ['nullable', 'integer', 'min:1', 'max:100000000'],
        ]);

        if ((int) $data['tamano_muestra'] > config('operations.sampling_warning_fruits') && !$request->boolean('confirmar_carga')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['confirmar_carga' => 'Esta muestra requiere identificar y revisar una cantidad elevada de frutos. Confirma la carga de trabajo antes de continuar.']);
        }
        unset($data['confirmar_carga']);
        if (!empty($data['poblacion_estimada']) && (int) $data['tamano_muestra'] > (int) $data['poblacion_estimada']) throw \Illuminate\Validation\ValidationException::withMessages(['tamano_muestra' => 'La muestra no puede superar la población estimada de frutos.']);
        $sampling = \Illuminate\Support\Arr::only($data, ['protocolo', 'criterio_muestra', 'seleccion_frutos', 'poblacion_estimada']);
        if ($procedure) $sampling += ['procedure_id' => $procedure->id, 'max_frutos' => $procedure->max_frutos, 'tamano_recomendado' => $procedure->tamano_recomendado];
        $data = \Illuminate\Support\Arr::except($data, ['protocolo', 'criterio_muestra', 'seleccion_frutos', 'poblacion_estimada']);
        $data = app(\App\Services\MaestroCatalog::class)->validate($data, ['metodo' => 'metodo'], $empresaId);
        $muestreo = DB::transaction(function () use ($data, $empresaId, $request, $sampling) {
            $lote = Lote::query()->forCurrentCompany()->lockForUpdate()->findOrFail($data['id_lote']);
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
