<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{SolicitudAcceso, Empresa, User};
use App\Services\AccesoEmpresarial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule, ValidationException};

class SolicitudAccesoController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        return view('admin.solicitudes.page');
    }

    public function show(Request $request, int $solicitud, AccesoEmpresarial $service)
    {
        abort_unless($request->user()->isSuperAdmin(), 403); abort_unless($service->habilitado(), 503);
        $s = SolicitudAcceso::with(['empresa', 'usuario', 'revisor'])->findOrFail($solicitud);
        $duplicada = Empresa::where('ruc', $s->ruc)->exists() || User::whereRaw('LOWER(correo) = ?', [mb_strtolower($s->correo)])->exists();
        $correoDisponible = $service->correoDisponible();
        return view('admin.solicitudes.show', compact('s', 'duplicada', 'correoDisponible'));
    }

    public function decidir(Request $request, int $solicitud, AccesoEmpresarial $service)
    {
        abort_unless($request->user()->isSuperAdmin(), 403); abort_unless($service->habilitado(), 503);
        $data = $request->validate(['decision' => ['required', Rule::in(['EN_REVISION', 'APROBADA', 'RECHAZADA'])], 'motivo' => ['required', 'string', 'max:2000']]);
        try { $s = $service->decidir($solicitud, $data['decision'], $data['motivo']); }
        catch (\Illuminate\Database\QueryException $e) { if (in_array((string) $e->getCode(), ['23505', '23000'], true)) throw ValidationException::withMessages(['decision' => 'El RUC o correo fue registrado por otra operación. Actualiza el expediente antes de decidir.']); throw $e; }
        $enviado = $s->estado === 'APROBADA' ? $service->invitar($s->id) : ($s->estado === 'RECHAZADA' ? $service->notificarRechazo($s) : true);
        return redirect()->route('admin.solicitudes.show', $s->id)->with($enviado ? 'success' : 'error', $enviado ? 'Decisión registrada correctamente.' : 'Decisión registrada; el correo no se pudo enviar. Configura el servicio de correo y reintenta el envío. Las cuentas invitadas siguen sin acceso hasta su activación.');
    }

    public function reenviar(Request $request, int $solicitud, AccesoEmpresarial $service)
    {
        abort_unless($request->user()->isSuperAdmin(), 403); abort_unless($service->habilitado(), 503);
        $s = SolicitudAcceso::findOrFail($solicitud); abort_if($s->activada_en, 422, 'La cuenta ya fue activada.');
        abort_unless(in_array($s->estado, ['APROBADA', 'RECHAZADA'], true), 422);
        $ok = $s->estado === 'APROBADA' ? $service->invitar($s->id) : $service->notificarRechazo($s);
        return back()->with($ok ? 'success' : 'error', $ok ? 'Correo enviado.' : 'No se pudo enviar el correo. Revisa la configuración del servicio.');
    }
}
