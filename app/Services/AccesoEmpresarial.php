<?php

namespace App\Services;

use App\Models\{SolicitudAcceso, Empresa, User, Rol};
use Illuminate\Support\Facades\{DB, Mail, Schema};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccesoEmpresarial
{
    public function habilitado(): bool { return Schema::hasTable('solicitudes_acceso'); }

    public function auditar(SolicitudAcceso $solicitud, string $evento, string $motivo): array
    {
        return [...($solicitud->historial ?? []), ['evento' => $evento, 'motivo' => $motivo, 'responsable' => auth()->user()?->nombre_completo ?? 'Solicitante', 'id_usuario' => auth()->id(), 'fecha' => now()->toIso8601String()]];
    }

    public function decidir(int $id, string $decision, string $motivo): SolicitudAcceso
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
        // Query the provider before taking database locks or retrying a transaction.
        $declared = SolicitudAcceso::findOrFail($id);
        $verification = $decision === 'APROBADA' ? app(SunatRucService::class)->verificar($declared->ruc, $declared->razon_social) : null;
        return DB::transaction(function () use ($id, $decision, $motivo, $declared, $verification) {
            $s = SolicitudAcceso::lockForUpdate()->findOrFail($id);
            if ($s->ruc !== $declared->ruc || $s->razon_social !== $declared->razon_social) throw ValidationException::withMessages(['ruc' => 'La solicitud cambió durante la verificación. Actualiza e intenta nuevamente.']);
            if (in_array($s->estado, ['APROBADA', 'RECHAZADA'], true)) throw ValidationException::withMessages(['decision' => 'Esta solicitud ya fue decidida y no puede duplicarse.']);
            if ($decision === 'APROBADA') {
                if (Empresa::where('ruc', $s->ruc)->exists()) throw ValidationException::withMessages(['ruc' => 'Ya existe una empresa con este RUC. Revisa su expediente antes de decidir.']);
                if (User::whereRaw('LOWER(correo) = ?', [mb_strtolower($s->correo)])->exists()) throw ValidationException::withMessages(['correo' => 'El correo ya pertenece a una cuenta. No se reasignan cuentas ni permisos automáticamente.']);
                $rol = Rol::where('codigo', 'ADMIN')->where('estado', true)->first();
                if (!$rol) throw ValidationException::withMessages(['decision' => 'Configura un rol ADMIN activo antes de aprobar solicitudes.']);
                app(MaestroCatalog::class)->validate(['tipo_empresa' => $s->tipo_empresa], ['tipo_empresa' => 'tipo_empresa'], null);
                $empresa = Empresa::create(['ruc' => $s->ruc, 'razon_social' => $s->razon_social, 'nombre_comercial' => $s->nombre_comercial, 'tipo_empresa' => $s->tipo_empresa, 'correo' => $s->correo, 'telefono' => $s->telefono, 'estado' => true]);
                $empresa->update(['sunat_verificacion' => $verification]);
                $s->sunat_verificacion = $verification;
                $s->historial = $this->auditar($s, 'CONSULTA_RUC', $verification['mensaje']);
                $usuario = User::create(['id_empresa' => $empresa->id_empresa, 'id_rol' => $rol->id_rol, 'nombre' => $s->nombre, 'apellido' => $s->apellido, 'correo' => $s->correo, 'telefono' => $s->telefono, 'password_hash' => Str::random(64), 'estado' => false]);
                $s->id_empresa = $empresa->id_empresa; $s->id_usuario = $usuario->id_usuario;
            }
            $s->fill(['estado' => $decision, 'motivo' => $motivo, 'revisada_por' => auth()->id(), 'decidida_en' => $decision === 'EN_REVISION' ? null : now(), 'historial' => $this->auditar($s, $decision, $motivo)])->save();
            return $s;
        }, 3);
    }

    public function correoDisponible(): bool
    {
        $mailer = config('mail.default');
        return !in_array(config("mail.mailers.{$mailer}.transport", $mailer), ['log', 'array', 'failover'], true);
    }

    public function invitar(int $id): bool
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
        if (!$this->correoDisponible()) return false;
        try {
            return DB::transaction(function () use ($id) {
                $s = SolicitudAcceso::lockForUpdate()->with(['usuario', 'empresa'])->findOrFail($id);
                abort_unless($s->estado === 'APROBADA' && !$s->activada_en && $s->empresa?->estado && $s->usuario && !$s->usuario->estado, 422, 'La invitación no está disponible para esta solicitud.');
                $token = Str::random(64);
                $s->fill(['invitacion_hash' => hash('sha256', $token), 'invitacion_expira_en' => now()->addHours(72),
                    'historial' => $this->auditar($s, 'INVITACION_GENERADA', 'Enlace temporal de un solo uso. Sustituye el enlace anterior únicamente si el envío se completa.')])->save();
                $url = route('acceso.invitacion', ['solicitud' => $s->id, 'token' => $token]);
                // El bloqueo serializa reenvíos y activación. Si SMTP falla, se restaura
                // el token anterior por rollback; nunca guardar tokens en texto plano.
                Mail::send('emails.invitacion-empresa', compact('s', 'url'), fn ($mail) => $mail->to($s->correo)->subject('Activa tu acceso empresarial a AgroCloud AI'));
                $s->fill(['invitacion_enviada_en' => now(), 'historial' => $this->auditar($s, 'INVITACION_ENVIADA', 'El transporte de correo aceptó el mensaje; no confirma su recepción.')])->save();
                return true;
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            DB::transaction(function () use ($id) {
                $s = SolicitudAcceso::lockForUpdate()->findOrFail($id);
                $s->update(['historial' => $this->auditar($s, 'INVITACION_ENVIO_FALLIDO', 'No se pudo completar el envío. Se conserva la invitación anterior y se puede reintentar.')]);
            });
            return false;
        }
    }

    public function notificarRechazo(SolicitudAcceso $s): bool
    {
        if (!$this->correoDisponible()) return false;
        try { Mail::send('emails.solicitud-rechazada', compact('s'), fn ($mail) => $mail->to($s->correo)->subject('Resultado de tu solicitud a AgroCloud AI')); return true; }
        catch (\Throwable $e) { report($e); return false; }
    }
}
