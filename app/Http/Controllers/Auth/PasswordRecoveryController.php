<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordChanged;
use App\Support\PasswordPolicy;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Password};
use Illuminate\Support\Str;

class PasswordRecoveryController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    private function credentials(string $correo, bool $lock = false): array
    {
        return [fn ($q) => $q->whereRaw('LOWER(TRIM(correo)) = ?', [$correo])
            ->where('estado', true)->whereHas('rol', fn ($r) => $r->where('estado', true))
            ->where(fn ($company) => $company->whereNull('id_empresa')->orWhereHas('empresa', fn ($e) => $e->where('estado', true)))
            ->when($lock, fn ($query) => $query->lockForUpdate())];
    }

    private function email(Request $request): string
    {
        if (is_string($request->input('correo'))) {
            $request->merge(['correo' => mb_strtolower(trim($request->input('correo')))]);
        }
        return $request->validate(['correo' => ['required', 'string', 'email', 'max:255']])['correo'];
    }

    public function store(Request $request)
    {
        $correo = $this->email($request);
        try {
            Password::sendResetLink($this->credentials($correo));
        } catch (\Throwable $error) {
            // Mantener respuesta neutral incluso ante fallas del transporte.
            report($error);
        }
        return back()->with('status', 'Si la cuenta está activa y puede recuperar el acceso, recibirás un enlace para restablecer tu contraseña. Revisa también el correo no deseado.');
    }

    public function edit(Request $request, string $token)
    {
        $data = $request->validate(['email' => ['sometimes', 'nullable', 'string', 'email', 'max:255']]);
        return response()->view('auth.reset-password', ['token' => $token, 'correo' => $data['email'] ?? ''])
            ->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function update(Request $request)
    {
        $correo = $this->email($request);
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', ...PasswordPolicy::rules()],
        ]);
        $changed = null;
        $status = DB::transaction(function () use ($correo, $data, &$changed) {
            return Password::reset([...$this->credentials($correo, true), ...$data], function (User $user, string $password) use (&$changed) {
                $user->forceFill(['password_hash' => $password, 'remember_token' => Str::random(60)])->save();
                if (config('session.driver') === 'database') {
                    DB::table(config('session.table', 'sessions'))->where('user_id', $user->id_usuario)->delete();
                }
                $changed = $user;
                event(new PasswordReset($user));
            });
        });
        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput(['correo' => $correo])->withErrors(['correo' => 'El enlace no es válido, expiró o la cuenta no está disponible. Solicita un nuevo enlace.']);
        }
        rescue(fn () => $changed->notify(new PasswordChanged), report: true);
        return redirect()->route('login')->with('status', 'Contraseña actualizada. Inicia sesión con tu nueva contraseña.');
    }
}
