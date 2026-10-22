<?php

namespace App\Http\Controllers;

use App\Models\{Empresa, SolicitudAcceso, User};
use App\Services\{AccesoEmpresarial, MaestroCatalog, OperationalNotifier};
use Illuminate\Database\QueryException;
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

        $request->merge([
            'correo' => mb_strtolower(trim((string) $request->input('correo'))),
            'ruc' => trim((string) $request->input('ruc')),
        ]);

        $data = $request->validate([
            'ruc' => ['required', 'digits:11'],
            'razon_social' => ['required', 'string', 'max:200'],
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'tipo_empresa' => ['required', 'string', 'max:80', Rule::in(app(MaestroCatalog::class)->options('tipo_empresa')->pluck('nombre')->all())],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email:rfc', 'max:254'],
            'telefono' => ['required', 'string', 'max:30', new \App\Rules\PhoneNumber],
            'uso' => ['required', 'string', 'min:20', 'max:2000'],
            'consentimiento' => ['accepted'],
            'sitio_contacto' => ['nullable', 'max:0'],
        ]);
        unset($data['consentimiento'], $data['sitio_contacto']);

        try {
            $solicitud = DB::transaction(function () use ($data) {
                // El RUC identifica el expediente: nunca se crean expedientes paralelos.
                $previa = SolicitudAcceso::where('ruc', $data['ruc'])->lockForUpdate()->first();
                if ($previa && $previa->estado !== 'RECHAZADA') {
                    return null;
                }

                // Este modelo asocia cada usuario a UNA empresa (usuario.id_empresa).
                // No creamos solicitudes que luego no podrán aprobarse por correo duplicado.
                // Se responde igual a todos los visitantes para no enumerar usuarios/RUC.
                $correoEnOtroExpediente = SolicitudAcceso::query()
                    ->whereRaw('LOWER(correo) = ?', [$data['correo']])
                    ->where('ruc', '!=', $data['ruc'])
                    ->whereIn('estado', ['PENDIENTE', 'EN_REVISION', 'APROBADA'])
                    ->exists();
                if ($correoEnOtroExpediente
                    || User::whereRaw('LOWER(correo) = ?', [$data['correo']])->exists()
                    || Empresa::where('ruc', $data['ruc'])->exists()) {
                    return null;
                }

                if ($previa) {
                    $historial = [
                        ...($previa->historial ?? []),
                        [
                            'evento' => 'SOLICITUD_REENVIADA',
                            'responsable' => 'Solicitante',
                            'fecha' => now()->toIso8601String(),
                            'datos_previos' => $previa->only(['razon_social', 'nombre_comercial', 'tipo_empresa', 'nombre', 'apellido', 'correo', 'telefono', 'uso']),
                        ],
                    ];
                    $previa->fill($data + [
                        'estado' => 'PENDIENTE',
                        'motivo' => null,
                        'revisada_por' => null,
                        'decidida_en' => null,
                        'historial' => $historial,
                    ])->save();
                    return $previa;
                }

                return SolicitudAcceso::create($data + [
                    'estado' => 'PENDIENTE',
                    'historial' => [[
                        'evento' => 'SOLICITUD_RECIBIDA',
                        'responsable' => 'Solicitante',
                        'fecha' => now()->toIso8601String(),
                    ]],
                ]);
            }, 3);

            if ($solicitud) {
                OperationalNotifier::platform(
                    'SOLICITUD_ACCESO',
                    'Nueva solicitud de acceso',
                    $solicitud->razon_social.' solicita incorporación empresarial.',
                    route('admin.solicitudes.show', $solicitud->id)
                );
            }
        } catch (QueryException $e) {
            // Carrera de inserciones: la restricción UNIQUE(ruc) sigue siendo
            // la garantía definitiva. No se filtra la existencia de un RUC.
            if (!in_array((string) $e->getCode(), ['23505', '23000'], true)) {
                throw $e;
            }
        }

        return redirect()->route('acceso.recibida')->with(
            'status',
            'Formulario procesado. Si los datos son nuevos y elegibles, la administración revisará la solicitud.'
        );
    }

    private function validarInvitacion(SolicitudAcceso $solicitud, string $token): void
    {
        if (!(
            $solicitud->estado === 'APROBADA'
            && !$solicitud->activada_en
            && $solicitud->invitacion_expira_en?->isFuture()
            && $solicitud->invitacion_hash
            && hash_equals((string) $solicitud->invitacion_hash, hash('sha256', $token))
        )) {
            throw ValidationException::withMessages([
                'invitacion' => 'La invitación expiró, se reemplazó o ya fue utilizada. Solicita un enlace nuevo al administrador de AgroCloud.',
            ]);
        }
    }

    public function invitacion(int $solicitud, string $token, AccesoEmpresarial $service)
    {
        abort_unless($service->habilitado(), 503);
        $s = SolicitudAcceso::findOrFail($solicitud);
        try {
            $this->validarInvitacion($s, $token);
        } catch (ValidationException $e) {
            return response()->view('acceso.invitacion-expirada', [], 410)
                ->header('Cache-Control', 'no-store')
                ->header('Referrer-Policy', 'no-referrer');
        }
        return response()->view('acceso.activar', compact('s', 'token'))
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function activar(Request $request, int $solicitud, string $token, AccesoEmpresarial $service)
    {
        abort_unless($service->habilitado(), 503);
        $data = $request->validate([
            'password' => ['required', 'confirmed', 'max:255', Password::min(12)->mixedCase()->numbers()],
        ]);

        DB::transaction(function () use ($solicitud, $token, $data) {
            $s = SolicitudAcceso::lockForUpdate()->findOrFail($solicitud);
            $this->validarInvitacion($s, $token);
            $u = User::lockForUpdate()->findOrFail($s->id_usuario);

            if (!(
                $u->id_empresa === (int) $s->id_empresa
                && mb_strtolower($u->correo) === mb_strtolower($s->correo)
                && $u->rol?->codigo === 'ADMIN'
                && $u->rol?->estado
                && $u->empresa?->estado
                && !$u->estado
            )) {
                throw ValidationException::withMessages([
                    'invitacion' => 'Esta cuenta no está disponible para activación. Contacta a la administración de AgroCloud.',
                ]);
            }

            // El modelo User usa el cast "hashed" para password_hash.
            $u->update(['password_hash' => $data['password'], 'estado' => true, 'email_verified_at' => now()]);
            $s->update([
                'activada_en' => now(),
                'invitacion_hash' => null,
                'invitacion_expira_en' => null,
                'historial' => [
                    ...($s->historial ?? []),
                    [
                        'evento' => 'CUENTA_ACTIVADA',
                        'responsable' => $u->nombre_completo,
                        'id_usuario' => $u->id_usuario,
                        'fecha' => now()->toIso8601String(),
                    ],
                ],
            ]);
        }, 3);

        return redirect()->route('login')->with(
            'success',
            'Cuenta activada. Ingresa con el correo indicado en la invitación y la contraseña que acabas de establecer.'
        );
    }
}
