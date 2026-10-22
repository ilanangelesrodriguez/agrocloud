<?php

namespace App\Http\Controllers;

use App\Models\SolicitudAcceso;
use App\Services\{AccesoEmpresarial, MaestroCatalog, OperationalNotifier};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule, ValidationException};
use Illuminate\Validation\Rules\Password;

class SolicitudAccesoController extends Controller
{
    public function crear(AccesoEmpresarial $service)
    {
        $habilitado = $service->habilitado();
        $tipos = $habilitado ? app(MaestroCatalog::class)->options('tipo_empresa')->pluck('nombre') : collect();
        return view('acceso.solicitar', compact('habilitado', 'tipos'));
    }

    public function guardar(Request $request, AccesoEmpresarial $service)
    {
        abort_unless($service->habilitado(), 503, 'El registro de solicitudes todavía no está habilitado.');
        $request->merge(['correo' => mb_strtolower(trim((string) $request->input('correo'))), 'ruc' => trim((string) $request->input('ruc'))]);
        $data = $request->validate([
            'ruc' => ['required', 'digits:11'], 'razon_social' => ['required', 'string', 'max:200'],
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'tipo_empresa' => ['required', 'string', 'max:80', Rule::in(app(MaestroCatalog::class)->options('tipo_empresa')->pluck('nombre')->all())],
            'nombre' => ['required', 'string', 'max:100'], 'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email:rfc', 'max:254'], 'telefono' => ['required', 'string', 'max:30', new \App\Rules\PhoneNumber],
            'uso' => ['required', 'string', 'min:20', 'max:2000'], 'consentimiento' => ['accepted'], 'sitio_contacto' => ['nullable', 'max:0'],
        ]);
        unset($data['consentimiento'], $data['sitio_contacto']);
        try {
            $s = DB::transaction(function () use ($data) {
                $previa = SolicitudAcceso::where('ruc', $data['ruc'])->lockForUpdate()->first();
                if ($previa && $previa->estado !== 'RECHAZADA') return null;
                if ($previa) {
                    $history = [...($previa->historial ?? []), ['evento' => 'SOLICITUD_REENVIADA', 'responsable' => 'Solicitante', 'fecha' => now()->toIso8601String(), 'datos_previos' => $previa->only(['razon_social', 'nombre_comercial', 'tipo_empresa', 'nombre', 'apellido', 'correo', 'telefono', 'uso'])]];
                    $previa->fill($data + ['estado' => 'PENDIENTE', 'motivo' => null, 'revisada_por' => null, 'decidida_en' => null, 'historial' => $history])->save();
                    return $previa;
                }
                return SolicitudAcceso::create($data + ['estado' => 'PENDIENTE', 'historial' => [['evento' => 'SOLICITUD_RECIBIDA', 'responsable' => 'Solicitante', 'fecha' => now()->toIso8601String()]]]);
            }, 3);
            if ($s) OperationalNotifier::platform('SOLICITUD_ACCESO', 'Nueva solicitud de acceso', $s->razon_social.' solicita incorporación empresarial.', route('admin.solicitudes.show', $s->id));
        } catch (\Illuminate\Database\QueryException $e) {
            if (!in_array((string) $e->getCode(), ['23505', '23000'], true)) throw $e;
        }
        return redirect()->route('acceso.recibida')->with('success', 'Datos recibidos. La solicitud será revisada por la administración de AgroCloud.');
    }

    private function validarInvitacion(SolicitudAcceso $s, string $token): void
    {
        if (!($s->estado === 'APROBADA' && !$s->activada_en && $s->invitacion_expira_en?->isFuture() && hash_equals((string) $s->invitacion_hash, hash('sha256', $token)))) throw ValidationException::withMessages(['invitacion' => 'La invitación expiró o ya fue utilizada. Solicita un nuevo enlace a la administración de AgroCloud.']);
    }

    public function invitacion(int $solicitud, string $token, AccesoEmpresarial $service)
    {
        abort_unless($service->habilitado(), 503);
        $s = SolicitudAcceso::findOrFail($solicitud);
        try { $this->validarInvitacion($s, $token); }
        catch (ValidationException $e) { return response()->view('acceso.invitacion-expirada', [], 410)->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer'); }
        return response()->view('acceso.activar', compact('s', 'token'))->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function activar(Request $request, int $solicitud, string $token, AccesoEmpresarial $service)
    {
        abort_unless($service->habilitado(), 503);
        $data = $request->validate(['password' => ['required', 'confirmed', 'max:255', Password::min(12)->mixedCase()->numbers()]]);
        DB::transaction(function () use ($solicitud, $token, $data, $service) {
            $s = SolicitudAcceso::lockForUpdate()->findOrFail($solicitud); $this->validarInvitacion($s, $token);
            $u = \App\Models\User::lockForUpdate()->findOrFail($s->id_usuario);
            abort_unless($u->id_empresa === (int) $s->id_empresa && $u->correo === $s->correo && $u->rol?->codigo === 'ADMIN' && $u->rol?->estado && $u->empresa?->estado && !$u->estado, 422, 'La cuenta no está disponible para activación. Contacta a la administración.');
            $u->update(['password_hash' => $data['password'], 'estado' => true, 'email_verified_at' => now()]);
            $s->update(['activada_en' => now(), 'invitacion_hash' => null, 'invitacion_expira_en' => null, 'historial' => [...($s->historial ?? []), ['evento' => 'CUENTA_ACTIVADA', 'responsable' => $u->nombre_completo, 'id_usuario' => $u->id_usuario, 'fecha' => now()->toIso8601String()]]]);
        }, 3);
        return redirect()->route('login')->with('success', 'Cuenta activada. Inicia sesión con tu correo y la contraseña que acabas de establecer.');
    }
}
