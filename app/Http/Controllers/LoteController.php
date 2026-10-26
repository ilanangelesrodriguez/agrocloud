<?php

namespace App\Http\Controllers;

use App\Enums\LoteEstado;
use App\Models\Lote;
use App\Models\Productor;
use App\Services\MaestroCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoteController extends Controller
{
    /**
     * Listado de lotes.
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar'));

        $lotes = Lote::query()
            ->forCurrentCompany()
            ->with(['productor', 'recepcion'])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query
                        ->where('codigo_lote', 'ilike', "%{$buscar}%")
                        ->orWhere('producto', 'ilike', "%{$buscar}%")
                        ->orWhere('variedad', 'ilike', "%{$buscar}%")
                        ->orWhereHas('productor', function ($productorQuery) use ($buscar) {
                            $productorQuery
                                ->where('nombre_razon_social', 'ilike', "%{$buscar}%")
                                ->orWhere('ruc', 'ilike', "%{$buscar}%");
                        });
                });
            })
            ->orderByRaw(\App\Models\Lote::OPERATIONAL_DATE_SQL.' DESC NULLS LAST')
            ->orderByDesc('id_lote')
            ->paginate(10)
            ->withQueryString();

        return view('lotes.index', compact('lotes', 'buscar'));
    }

    /**
     * Formulario para registrar un nuevo lote.
     */
    public function create(): View
    {
        $productores = Productor::query()
            ->forCurrentCompany()
            ->orderBy('nombre_razon_social')
            ->get();

        $estados = [LoteEstado::PENDIENTE_RECEPCION];
        $codigoSugerido = 'LOT-'.now()->format('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(6));

        return view('lotes.create', compact('productores', 'estados', 'codigoSugerido'));
    }

    /**
     * Registrar un nuevo lote.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!$request->has('fecha_llegada_prevista') && $request->has('fecha_recepcion')) $request->merge(['fecha_llegada_prevista' => $request->input('fecha_recepcion')]);
        \App\Services\OperationalDate::normalize($request, ['fecha_llegada_prevista']);
        $data = $request->validate([
            'id_productor' => [
                'required',
                'integer',
            ],

            'codigo_lote' => [
                'required',
                'string',
                'max:50',
                Rule::unique('lote', 'codigo_lote')
                    ->where(function ($query) {
                        return $query->where(
                            'id_empresa',
                            auth()->user()->id_empresa
                        );
                    }),
            ],

            'producto' => [
                'required',
                'string',
                'max:150',
            ],

            'variedad' => [
                'nullable',
                'string',
                'max:150',
            ],

            'unidad_medida' => ['nullable', 'string', 'max:40'],
            'peso_kg' => [
                'required',
                'numeric',
                'min:0',
            ],

            'fecha_llegada_prevista' => [
                'required',
                'date',
            ],

            'estado' => [
                'sometimes',
                Rule::in(['PENDIENTE_RECEPCION', 'RECIBIDO']),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verificar que el productor pertenece a la empresa actual
        |--------------------------------------------------------------------------
        */

        $productor = Productor::query()
            ->forCurrentCompany()
            ->findOrFail($data['id_productor']);

        /*
        |--------------------------------------------------------------------------
        | Crear lote
        |--------------------------------------------------------------------------
        */

        $data['variedad'] = $data['variedad'] ?? null;
        $data['unidad_medida'] = $data['unidad_medida'] ?? 'Kilogramo';
        $catalog = app(MaestroCatalog::class);
        $data = $catalog->validate($data, ['producto' => 'producto', 'variedad' => 'variedad', 'unidad_medida' => 'unidad'], auth()->user()->id_empresa);
        $data['peso_kg'] = $catalog->pesoKg((float) $data['peso_kg'], $data['unidad_medida']);
        $loteCreado = Lote::create([
            'id_productor' => $productor->id_productor,
            'codigo_lote' => $data['codigo_lote'],
            'producto' => $data['producto'],
            'variedad' => $data['variedad'] ?? null,
            'peso_kg' => $data['peso_kg'],
            'unidad_medida' => $data['unidad_medida'],
            'fecha_llegada_prevista' => $data['fecha_llegada_prevista'],
            // Legacy column retained for integrations; the planned date is never overwritten by reception.
            'fecha_recepcion' => $data['fecha_llegada_prevista'],
            'estado' => LoteEstado::PENDIENTE_RECEPCION,
        ]);

        // AGROCLOUD_NOTIFICATIONS_INSTALLED
        \App\Services\OperationalNotifier::roles(
            (int) $loteCreado->id_empresa, ['RECEPCION'], 'LOTE_REGISTRADO',
            'Nuevo lote pendiente de recepción',
            'El lote '.$loteCreado->codigo_lote.' requiere confirmación de ingreso físico.',
            '/recepciones', 'normal', (int) $loteCreado->id_lote, (int) auth()->id()
        );

        return redirect()
            ->route(auth()->user()->hasPermission('recepciones.crear') ? 'recepciones.index' : 'lotes.index')
            ->with(
                'success',
                'El lote fue registrado correctamente.'
            );
    }

    /**
     * Mostrar detalle de un lote.
     */
    public function show(int $lote): View
    {
        $loteModel = Lote::query()
            ->forCurrentCompany()
            ->with(['productor', 'recepcion.responsable', 'muestreos.inspector', 'muestreos.inspecciones.inspector', 'muestreos.inspecciones.dictamen.supervisor'])
            ->findOrFail($lote);

        $incidencias = collect(app(\App\Services\IncidenciasOperativas::class)->reconstruir(app(\App\Services\IncidenciasOperativas::class)->consulta($loteModel->id_empresa, $loteModel->id_lote)->get()));
        $abiertas = $incidencias->where('estado', '!=', 'CERRADA')->count();
        $bloqueado = $incidencias->contains(fn ($i) => in_array($i->gravedad, ['ALTA', 'CRITICA'], true) && $i->estado !== 'CERRADA');
        $ultimaInspeccion = $loteModel->muestreos->flatMap->inspecciones->sortByDesc('id_inspeccion')->first();
        $protocolos = DB::table('eventos_trazabilidad')->where('id_empresa', $loteModel->id_empresa)->where('entidad', 'Muestreo')->where('evento', 'PROTOCOLO_MUESTREO')->whereIn('registro', $loteModel->muestreos->modelKeys())->orderByDesc('id')->get()->unique('registro')->mapWithKeys(fn ($e) => [$e->registro => json_decode($e->cambios, true)['protocolo'] ?? null]);
        $usuario = auth()->user();
        $accion = ['titulo' => 'Consulta el expediente', 'descripcion' => 'El lote conserva sus operaciones y decisiones para consulta.', 'url' => null, 'icono' => 'eye'];
        if ($loteModel->estado === LoteEstado::ANULADO) $accion = ['titulo' => 'Lote anulado', 'descripcion' => 'Este lote no puede ingresar a recepción. Su expediente e historial permanecen disponibles.', 'url' => null, 'icono' => 'clipboard-list'];
        elseif ($bloqueado) $accion = ['titulo' => 'Atender incidencias bloqueantes', 'descripcion' => 'El supervisor debe verificar y cerrar las incidencias altas o críticas antes de liberar el lote.', 'url' => route('incidencias.index', ['lote' => $loteModel->id_lote]), 'icono' => 'alert-triangle'];
        elseif (!$loteModel->recepcion && $loteModel->muestreos->isEmpty()) $accion = ['titulo' => 'Confirmar ingreso físico', 'descripcion' => 'Esta operación corresponde al personal de Recepción. El registro del lote no confirma su ingreso.', 'url' => $usuario->rol?->codigo === 'RECEPCION' && $usuario->hasPermission('recepciones.crear') && $usuario->hasPermission('recepciones.ver') ? route('recepciones.revisar', $loteModel->id_lote) : null, 'icono' => 'building'];
        elseif ($loteModel->muestreos->isEmpty()) $accion = ['titulo' => 'Crear muestreo', 'descripcion' => 'El lote tiene recepción confirmada. El inspector puede registrar la muestra y su protocolo.', 'url' => $usuario->rol?->codigo === 'INSPECTOR' && $usuario->hasPermission('muestreos.crear') ? route('muestreos.create', ['lote' => $loteModel->id_lote]) : null, 'icono' => 'clipboard-list'];
        elseif (!$ultimaInspeccion) $accion = ['titulo' => 'Iniciar inspección', 'descripcion' => 'El inspector debe iniciar la captura desde el muestreo registrado.', 'url' => $usuario->rol?->codigo === 'INSPECTOR' && $usuario->hasPermission('muestreos.ver') ? route('muestreos.show', $loteModel->muestreos->sortByDesc('id_muestreo')->first()->id_muestreo) : null, 'icono' => 'camera'];
        elseif ($ultimaInspeccion->estado === 'EN_CAPTURA') $accion = ['titulo' => $ultimaInspeccion->solicitada_por ? 'Continuar reinspección' : 'Continuar inspección', 'descripcion' => 'La captura y la revisión humana corresponden al inspector asignado. El dictamen anterior se conserva.', 'url' => $usuario->hasPermission('inspecciones.ver') ? route('inspecciones.show', $ultimaInspeccion->id_inspeccion) : null, 'icono' => 'camera'];
        elseif (!$ultimaInspeccion->dictamen) $accion = ['titulo' => 'Pendiente de dictamen', 'descripcion' => 'La inspección está validada. El supervisor debe revisar el expediente y emitir su decisión.', 'url' => $usuario->rol?->codigo === 'SUPERVISOR' && $usuario->hasPermission('calidad.ver') ? route('calidad.show', $ultimaInspeccion->id_inspeccion) : null, 'icono' => 'shield'];
        elseif ($ultimaInspeccion->dictamen->decision === 'OBSERVADO' && in_array($ultimaInspeccion->dictamen->tipo_observacion, ['DOCUMENTACION', 'CORRECCION_ADMINISTRATIVA'], true)) $accion = ['titulo' => 'Revisar corrección pendiente', 'descripcion' => 'Responsables: Administración aporta la documentación o corrección y el supervisor la verifica antes de emitir una nueva decisión. No se requieren fotografías nuevas.', 'url' => $usuario->hasPermission('calidad.ver') ? route('calidad.show', $ultimaInspeccion->id_inspeccion) : null, 'icono' => 'shield'];
        else $accion = ['titulo' => 'Dictamen '.mb_strtolower($ultimaInspeccion->dictamen->decision), 'descripcion' => 'Decisión registrada por '.$ultimaInspeccion->dictamen->supervisor?->nombre_completo.'. Consulta su fundamento en el expediente.', 'url' => $usuario->hasPermission('calidad.ver') ? route('calidad.show', $ultimaInspeccion->id_inspeccion) : null, 'icono' => 'shield'];
        $eventos = auth()->user()->hasPermission('trazabilidad.ver') ? DB::table('eventos_trazabilidad')->where('id_empresa', auth()->user()->id_empresa)->where('id_lote', $loteModel->id_lote)->latest('id')->limit(15)->get() : collect();
        return view('lotes.show', [
            'eventos' => $eventos, 'abiertas' => $abiertas, 'bloqueado' => $bloqueado, 'ultimaInspeccion' => $ultimaInspeccion, 'accion' => $accion, 'protocolos' => $protocolos,
            'lote' => $loteModel,
        ]);
    }

    /**
     * Formulario para editar un lote.
     */
    public function edit(int $lote): View
    {
        $loteModel = Lote::query()
            ->forCurrentCompany()
            ->findOrFail($lote);

        abort_if($loteModel->estado === LoteEstado::ANULADO || $loteModel->recepcion()->exists() || $loteModel->muestreos()->exists(), 422, 'El lote ya ingresó al flujo operativo. Sus datos se conservan para trazabilidad.');

        $productores = Productor::query()
            ->forCurrentCompany()
            ->orderBy('nombre_razon_social')
            ->get();

        $estados = [$loteModel->estado];

        return view('lotes.edit', [
            'lote' => $loteModel,
            'productores' => $productores,
            'estados' => $estados,
        ]);
    }

    /**
     * Actualizar un lote.
     */
    public function update(
        Request $request,
        int $lote
    ): RedirectResponse {
        return DB::transaction(function () use ($request, $lote) {
            $loteModel = Lote::query()
                ->forCurrentCompany()
                ->lockForUpdate()
                ->findOrFail($lote);

            abort_if($loteModel->estado === LoteEstado::ANULADO || $loteModel->recepcion()->exists() || $loteModel->muestreos()->exists(), 422, 'El lote ya ingresó al flujo operativo. Sus datos se conservan para trazabilidad.');

            if (!$request->has('fecha_llegada_prevista') && $request->has('fecha_recepcion')) $request->merge(['fecha_llegada_prevista' => $request->input('fecha_recepcion')]);
        \App\Services\OperationalDate::normalize($request, ['fecha_llegada_prevista']);
            $data = $request->validate([
                'id_productor' => [
                    'required',
                    'integer',
                ],

                'codigo_lote' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('lote', 'codigo_lote')
                        ->where(function ($query) {
                            return $query->where(
                                'id_empresa',
                                auth()->user()->id_empresa
                            );
                        })
                        ->ignore($loteModel->id_lote, 'id_lote'),
                ],

                'producto' => [
                'required',
                'string',
                'max:150',
            ],

                'variedad' => [
                'nullable',
                'string',
                'max:150',
            ],

                'unidad_medida' => ['nullable', 'string', 'max:40'],
                'peso_kg' => [
                'required',
                'numeric',
                'min:0',
            ],

                'fecha_llegada_prevista' => [
                'required',
                'date',
            ],

                'estado' => [
                'sometimes',
                Rule::in([$loteModel->estado->value]),
            ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Verificar que el productor pertenece a la empresa actual
            |--------------------------------------------------------------------------
            */

            $productor = Productor::query()
                ->forCurrentCompany()
                ->findOrFail($data['id_productor']);

            /*
            |--------------------------------------------------------------------------
            | Actualizar
            |--------------------------------------------------------------------------
            */

            $data['variedad'] = $data['variedad'] ?? null;
            $data['unidad_medida'] = $data['unidad_medida'] ?? 'Kilogramo';
            $catalog = app(MaestroCatalog::class);
            $data = $catalog->validate($data, ['producto' => 'producto', 'variedad' => 'variedad', 'unidad_medida' => 'unidad'], auth()->user()->id_empresa, $loteModel->toArray());
            $data['peso_kg'] = $catalog->pesoKg((float) $data['peso_kg'], $data['unidad_medida']);
            $loteModel->update([
                'id_productor' => $productor->id_productor,
                'codigo_lote' => $data['codigo_lote'],
                'producto' => $data['producto'],
                'variedad' => $data['variedad'] ?? null,
                'peso_kg' => $data['peso_kg'],
                'unidad_medida' => $data['unidad_medida'],
                'fecha_llegada_prevista' => $data['fecha_llegada_prevista'],
            // Legacy column retained for integrations; the planned date is never overwritten by reception.
            'fecha_recepcion' => $data['fecha_llegada_prevista'],
                'estado' => $loteModel->estado,
            ]);

            return redirect()
                ->route('lotes.show', $loteModel->id_lote)
                ->with(
                    'success',
                    'El lote fue actualizado correctamente.'
                );
        });
    }


    /** La ruta histórica de eliminación ahora conserva el registro y su historial. */
    public function destroy(int $lote): RedirectResponse
    {
        app(\App\Services\AnularLote::class)->ejecutar($lote);
        return redirect()->route('lotes.index')->with('success', 'Lote anulado. Su historial se conserva para trazabilidad.');
    }
}
