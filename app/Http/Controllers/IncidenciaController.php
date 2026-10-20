<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\User;
use App\Services\IncidenciasOperativas;
use App\Services\OperationalNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IncidenciaController extends Controller
{
    public function index(Request $request, IncidenciasOperativas $service)
    {
        $request->validate(['lote' => ['nullable', 'integer']]);
        $lote = $request->filled('lote') ? Lote::forCurrentCompany()->findOrFail($request->integer('lote')) : null;
        $eventos = $service->consulta((int) $request->user()->id_empresa, $lote?->id_lote)->orderByDesc('id')->paginate(20)->withQueryString();
        $incidencias = $service->reconstruir($eventos->items());
        $responsables = User::where('id_empresa', $request->user()->id_empresa)->where('estado', true)->whereHas('rol', fn ($q) => $q->whereIn('codigo', ['ADMIN', 'RECEPCION', 'INSPECTOR', 'SUPERVISOR', 'PRODUCCION']))->orderBy('nombre')->get()->filter(fn ($user) => $user->hasPermission('lotes.ver'));
        $lotes = Lote::forCurrentCompany()->whereIn('id_lote', collect($incidencias)->pluck('id_lote'))->get()->keyBy('id_lote');
        $evidencias = $lote ? \App\Models\EvidenciaInspeccion::forCurrentCompany()->whereHas('inspeccion.muestreo', fn ($q) => $q->where('id_lote', $lote->id_lote))->latest('id_evidencia')->limit(100)->get() : collect();
        return view('operaciones.incidencias', compact('lote', 'lotes', 'incidencias', 'eventos', 'responsables', 'evidencias'));
    }

    public function crear(Request $request, int $lote)
    {
        abort_unless(in_array($request->user()->rol?->codigo, ['RECEPCION', 'INSPECTOR', 'SUPERVISOR', 'ADMIN'], true), 403);
        $lote = Lote::forCurrentCompany()->findOrFail($lote);
        $responsables = User::where('id_empresa', $lote->id_empresa)->where('estado', true)->with('rol')->orderBy('nombre')->get()->filter(fn ($user) => $user->hasPermission('lotes.ver'));
        return view($request->header('X-Workspace-Drawer') ? 'operaciones._incidencia-form' : 'operaciones.incidencia-create', compact('lote', 'responsables'));
    }

    public function store(Request $request, int $lote, IncidenciasOperativas $service)
    {
        abort_unless(in_array($request->user()->rol?->codigo, ['RECEPCION', 'INSPECTOR', 'SUPERVISOR', 'ADMIN'], true), 403);
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:150'], 'descripcion' => ['required', 'string', 'max:3000'],
            'gravedad' => ['required', Rule::in(['BAJA', 'MEDIA', 'ALTA', 'CRITICA'])],
            'responsable' => ['required', 'integer', Rule::exists('usuario', 'id_usuario')->where('id_empresa', $request->user()->id_empresa)->where('estado', true)],
        ]);
        abort_unless(User::where('id_empresa', $request->user()->id_empresa)->findOrFail($data['responsable'])->hasPermission('lotes.ver'), 422, 'El responsable necesita acceso al expediente de lotes.');
        DB::transaction(function () use ($request, $lote, $data, $service) {
            $lot = Lote::forCurrentCompany()->lockForUpdate()->findOrFail($lote);
            if (in_array($data['gravedad'], ['ALTA', 'CRITICA'], true) && $lot->estado === \App\Enums\LoteEstado::CLASIFICADO) {
                $lot->update(['estado' => \App\Enums\LoteEstado::RETENIDO]);
                OperationalNotifier::roles($lot->id_empresa, ['PRODUCCION', 'SUPERVISOR'], 'LOTE_RETENIDO', 'Lote retenido por incidencia', $data['titulo'], '/lotes/'.$lot->id_lote, 'alta', $lot->id_lote);
            }
            $service->registrar($lot->id_empresa, $lot->id_lote, 0, 'INCIDENCIA_ABIERTA', $data + ['estado' => 'ABIERTA']);
            OperationalNotifier::user($lot->id_empresa, (int) $data['responsable'], 'INCIDENCIA_ASIGNADA', 'Incidencia asignada', $data['titulo'], '/incidencias?lote='.$lot->id_lote, 'alta', $lot->id_lote);
            if (in_array($data['gravedad'], ['ALTA', 'CRITICA'], true)) OperationalNotifier::roles($lot->id_empresa, ['SUPERVISOR'], 'INCIDENCIA_CRITICA', 'Incidencia requiere revisión', $data['titulo'], '/incidencias?lote='.$lot->id_lote, 'alta', $lot->id_lote);
        });
        if ($request->header('X-Workspace-Drawer')) return response()->json(['saved' => true, 'module' => 'incidencias', 'message' => 'Incidencia registrada y responsable notificado.']);
        return redirect()->route('incidencias.index', ['lote' => $lote])->with('success', 'Incidencia registrada y responsable notificado.');
    }

    public function update(Request $request, int $incidencia, IncidenciasOperativas $service)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(['EN_ATENCION', 'RESUELTA', 'CERRADA', 'ABIERTA'])],
            'accion' => ['required', 'string', 'max:3000'],
            'evidencia' => ['nullable', 'integer'],
            'comprobacion' => ['nullable', 'string', 'max:1500', Rule::requiredIf(in_array($request->input('estado'), ['RESUELTA', 'CERRADA'], true))],
        ]);
        DB::transaction(function () use ($request, $incidencia, $data, $service) {
            $apertura = $service->consulta((int) $request->user()->id_empresa)->where('id', $incidencia)->first();
            abort_unless($apertura, 404);
            // El mismo bloqueo que el dictamen evita liberar mientras se abre o cambia una incidencia.
            $lot = Lote::forCurrentCompany()->lockForUpdate()->findOrFail($apertura->id_lote);
            $actual = $service->reconstruir([$apertura])[0];
            $supervisor = $request->user()->rol?->codigo === 'SUPERVISOR';
            abort_unless($supervisor || (int) $actual->responsable === (int) $request->user()->id_usuario, 403, 'Solo el responsable o el supervisor puede atender esta incidencia.');
            $permitidos = match ($actual->estado) {
                'ABIERTA' => ['EN_ATENCION'], 'EN_ATENCION' => ['RESUELTA'],
                'RESUELTA' => $supervisor ? ['CERRADA', 'EN_ATENCION'] : [],
                'CERRADA' => $supervisor ? ['ABIERTA'] : [], default => [],
            };
            abort_unless(in_array($data['estado'], $permitidos, true), 422, 'Transición no permitida. El supervisor verifica y cierra las incidencias resueltas.');
            if (!empty($data['evidencia'])) {
                abort_unless(\App\Models\EvidenciaInspeccion::forCurrentCompany()->whereHas('inspeccion.muestreo', fn ($q) => $q->where('id_lote', $apertura->id_lote))->whereKey($data['evidencia'])->exists(), 422, 'La fotografía debe pertenecer al lote de la incidencia.');
            }
            $service->registrar($apertura->id_empresa, $apertura->id_lote, $incidencia, 'INCIDENCIA_SEGUIMIENTO', $data);
            $bloqueado = $service->bloqueaLiberacion($apertura->id_empresa, $apertura->id_lote);
            if ($bloqueado && $lot->estado === \App\Enums\LoteEstado::CLASIFICADO) {
                $lot->update(['estado' => \App\Enums\LoteEstado::RETENIDO]);
                OperationalNotifier::roles($lot->id_empresa, ['PRODUCCION', 'SUPERVISOR'], 'LOTE_RETENIDO', 'Lote retenido por incidencia reabierta', $actual->titulo, '/lotes/'.$lot->id_lote, 'alta', $lot->id_lote);
            }
            if (!$bloqueado && $lot->estado === \App\Enums\LoteEstado::RETENIDO) {
                $lot->update(['estado' => \App\Enums\LoteEstado::CLASIFICADO]);
                OperationalNotifier::roles($lot->id_empresa, ['PRODUCCION'], 'LOTE_LIBERADO', 'Incidencias cerradas: lote liberado', $lot->codigo_lote, '/lotes/'.$lot->id_lote, 'normal', $lot->id_lote);
            }
            if ($data['estado'] === 'RESUELTA') OperationalNotifier::roles($apertura->id_empresa, ['SUPERVISOR'], 'INCIDENCIA_RESUELTA', 'Verifica el cierre de una incidencia', $actual->titulo, '/incidencias?lote='.$apertura->id_lote, 'alta', $apertura->id_lote);
        });
        return back()->with('success', 'Seguimiento registrado en el historial.');
    }
}
