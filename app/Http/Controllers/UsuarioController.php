<?php

namespace App\Http\Controllers;

use App\Events\CompanySecurityUpdated;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Rol;
use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = $this->empresaId();
        $search = trim((string) $request->string('q'));

        $usuarios = User::query()
            ->with('rol')
            ->where('id_empresa', $empresaId)
            ->where(function ($query) {
                $query->whereDoesntHave('rol')
                    ->orWhereHas('rol', fn ($roleQuery) => $roleQuery->where('codigo', '!=', 'SUPERADMIN'));
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%")
                        ->orWhere('cargo', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(12)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios', 'search'));
    }

    public function create(): View
    {
        $roles = $this->companyRoles();

        return view('usuarios.create', compact('roles'));
    }

    public function store(StoreUserRequest $request, CloudinaryService $cloudinary): RedirectResponse|JsonResponse
    {
        $data = $request->safe()->except(['password', 'password_confirmation', 'foto_perfil', 'motivo_seguridad']);
        $data = app(\App\Services\MaestroCatalog::class)->validate($data, ['cargo' => 'cargo'], $this->empresaId(), isset($usuario) ? $usuario->toArray() : []);
        $data['id_empresa'] = $this->empresaId();
        $data['password_hash'] = \Illuminate\Support\Str::random(64);
        $data['estado'] = true;
        $uploaded = null;

        try {
            if ($request->hasFile('foto_perfil')) {
                $uploaded = $cloudinary->uploadImage($request->file('foto_perfil'), 'agrocloud/perfiles');
                $data['foto_perfil_url'] = $uploaded['url'];
                $data['foto_perfil_public_id'] = $uploaded['public_id'];
            }

            $usuario = DB::transaction(function () use ($data) {
                $user = User::query()->create($data);
                $user->forceFill(['invitation_hash' => hash('sha256', \Illuminate\Support\Str::random(64))])->save();
                return $user;
            });
        } catch (Throwable $e) {
            if ($uploaded) {
                rescue(fn () => $cloudinary->deleteImage($uploaded['public_id']), report: true);
            }

            report($e);

            if ($request->header('X-User-Module') === 'true') {
                return response()->json(['errors' => ['foto_perfil' => ['No se pudo guardar el usuario. Revisa los datos o intenta subir la foto nuevamente.']]], 422);
            }
            return back()->withInput()->withErrors([
                'foto_perfil' => 'No se pudo crear el usuario. Si subiste una foto, revisa Cloudinary.',
            ]);
        }

        CompanySecurityUpdated::dispatch($data['id_empresa'], 'usuario', 'created', $usuario->id_usuario);

        return $this->savedResponse($request, 'Usuario creado. '.app(\App\Services\UserInvitation::class)->send($usuario));
    }

    public function edit(User $usuario): View
    {
        $this->assertManageableUser($usuario);
        $roles = $this->companyRoles();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $usuario, CloudinaryService $cloudinary): RedirectResponse|JsonResponse
    {
        $this->assertManageableUser($usuario);
        $data = $request->safe()->except(['password', 'password_confirmation', 'foto_perfil', 'motivo_seguridad']);
        $data = app(\App\Services\MaestroCatalog::class)->validate($data, ['cargo' => 'cargo'], $this->empresaId(), isset($usuario) ? $usuario->toArray() : []);

        // Un administrador no puede dejar su propia cuenta sin acceso desde este módulo.
        if ($usuario->is($request->user())) {
            $data['id_rol'] = $usuario->id_rol;
            $data['estado'] = true;
        } else {
            $data['estado'] = $request->boolean('estado');
        }

        $oldPublicId = $usuario->foto_perfil_public_id;
        $uploaded = null;

        try {
            if ($request->hasFile('foto_perfil')) {
                $uploaded = $cloudinary->uploadImage($request->file('foto_perfil'), 'agrocloud/perfiles');
                $data['foto_perfil_url'] = $uploaded['url'];
                $data['foto_perfil_public_id'] = $uploaded['public_id'];
            }

            DB::transaction(function () use ($usuario, $data) {
                if ($usuario->invitation_hash) {
                    if ($data['correo'] !== $usuario->correo) {
                        $usuario->forceFill(['invitation_hash' => hash('sha256', \Illuminate\Support\Str::random(64)), 'invitation_expires_at' => null, 'invitation_delivery_status' => 'not_sent']);
                    }
                } else {
                    app(\App\Services\EmailChange::class)->prepare($usuario, $data);
                }
                $usuario->update($data);
            });
        } catch (Throwable $e) {
            if ($uploaded) {
                rescue(fn () => $cloudinary->deleteImage($uploaded['public_id']), report: true);
            }

            report($e);

            if ($request->header('X-User-Module') === 'true') {
                return response()->json(['errors' => ['foto_perfil' => ['No se pudo guardar el usuario. Revisa los datos o intenta subir la foto nuevamente.']]], 422);
            }
            return back()->withInput()->withErrors([
                'foto_perfil' => 'No se pudo actualizar el usuario. Si subiste una foto, revisa Cloudinary.',
            ]);
        }

        if ($uploaded && $oldPublicId) {
            rescue(fn () => $cloudinary->deleteImage($oldPublicId), report: true);
        }

        CompanySecurityUpdated::dispatch($this->empresaId(), 'usuario', 'updated', $usuario->id_usuario);

        return $this->savedResponse($request, 'Usuario actualizado. Si cambiaste el correo, el anterior sigue vigente hasta verificar el nuevo; guarda los cambios y revisa la sección Seguridad y acceso.');
    }

    public function toggleStatus(Request $request, User $usuario): RedirectResponse|JsonResponse
    {
        $this->assertManageableUser($usuario);
        abort_if($usuario->is(auth()->user()), 422, 'No puedes desactivar tu propio usuario desde esta pantalla.');

        $request->validate(['estado_actual' => ['sometimes', 'required', 'boolean']]);
        DB::transaction(function () use ($request, $usuario) {
            $current = User::query()->where('id_empresa', $this->empresaId())->lockForUpdate()->findOrFail($usuario->id_usuario);
            $this->assertManageableUser($current);
            if ($request->has('estado_actual') && (bool) $current->estado !== $request->boolean('estado_actual')) {
                throw \Illuminate\Validation\ValidationException::withMessages(['estado' => 'El estado de esta cuenta cambió desde que abriste la lista. Actualiza los resultados antes de intentarlo nuevamente.']);
            }
            $request->validate(['motivo_seguridad' => [$current->estado ? 'required' : 'nullable', 'string', 'max:500']]);
            $current->update(['estado' => ! $current->estado]);
        });
        CompanySecurityUpdated::dispatch($this->empresaId(), 'usuario', 'status_changed', $usuario->id_usuario);

        return $this->savedResponse($request, 'Estado del usuario actualizado.');
    }

    public function show(User $usuario): View
    {
        $this->assertManageableUser($usuario);
        $usuario->loadMissing('rol.permisos');

        return view('usuarios.show', compact('usuario'));
    }

    private function savedResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->header('X-User-Module') === 'true') {
            return response()->json(['saved' => true, 'message' => $message]);
        }

        return redirect()->route('usuarios.index')->with('success', $message);
    }

    private function companyRoles()
    {
        return Rol::query()->with('permisos')
            ->where('estado', true)
            ->where('codigo', '!=', 'SUPERADMIN')
            ->orderBy('nombre')
            ->get();
    }

    private function empresaId(): int
    {
        $empresaId = auth()->user()?->id_empresa;
        abort_unless($empresaId, 403, 'Tu usuario no tiene una empresa asignada.');

        return (int) $empresaId;
    }

    private function assertManageableUser(User $usuario): void
    {
        abort_unless((int) $usuario->id_empresa === $this->empresaId(), 404);
        $usuario->loadMissing('rol');
        abort_if($usuario->rol?->codigo === 'SUPERADMIN', 404);
    }
}
