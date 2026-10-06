<?php

namespace App\Http\Controllers;

use App\Events\CompanySecurityUpdated;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Rol;
use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreUserRequest $request, CloudinaryService $cloudinary): RedirectResponse
    {
        $data = $request->safe()->except(['password', 'password_confirmation', 'foto_perfil']);
        $data['id_empresa'] = $this->empresaId();
        $data['password_hash'] = $request->string('password')->toString();
        $data['estado'] = $request->boolean('estado');
        $uploaded = null;

        try {
            if ($request->hasFile('foto_perfil')) {
                $uploaded = $cloudinary->uploadImage($request->file('foto_perfil'), 'agrocloud/perfiles');
                $data['foto_perfil_url'] = $uploaded['url'];
                $data['foto_perfil_public_id'] = $uploaded['public_id'];
            }

            $usuario = DB::transaction(fn () => User::query()->create($data));
        } catch (Throwable $e) {
            if ($uploaded) {
                rescue(fn () => $cloudinary->deleteImage($uploaded['public_id']), report: true);
            }

            report($e);

            return back()->withInput()->withErrors([
                'foto_perfil' => 'No se pudo crear el usuario. Si subiste una foto, revisa Cloudinary.',
            ]);
        }

        CompanySecurityUpdated::dispatch($data['id_empresa'], 'usuario', 'created', $usuario->id_usuario);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        $this->assertManageableUser($usuario);
        $roles = $this->companyRoles();

        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $usuario, CloudinaryService $cloudinary): RedirectResponse
    {
        $this->assertManageableUser($usuario);
        $data = $request->safe()->except(['password', 'password_confirmation', 'foto_perfil']);

        // Un administrador no puede dejar su propia cuenta sin acceso desde este módulo.
        if ($usuario->is($request->user())) {
            $data['id_rol'] = $usuario->id_rol;
            $data['estado'] = true;
        } else {
            $data['estado'] = $request->boolean('estado');
        }

        if ($request->filled('password')) {
            $data['password_hash'] = $request->string('password')->toString();
        }

        $oldPublicId = $usuario->foto_perfil_public_id;
        $uploaded = null;

        try {
            if ($request->hasFile('foto_perfil')) {
                $uploaded = $cloudinary->uploadImage($request->file('foto_perfil'), 'agrocloud/perfiles');
                $data['foto_perfil_url'] = $uploaded['url'];
                $data['foto_perfil_public_id'] = $uploaded['public_id'];
            }

            DB::transaction(fn () => $usuario->update($data));
        } catch (Throwable $e) {
            if ($uploaded) {
                rescue(fn () => $cloudinary->deleteImage($uploaded['public_id']), report: true);
            }

            report($e);

            return back()->withInput()->withErrors([
                'foto_perfil' => 'No se pudo actualizar el usuario. Si subiste una foto, revisa Cloudinary.',
            ]);
        }

        if ($uploaded && $oldPublicId) {
            rescue(fn () => $cloudinary->deleteImage($oldPublicId), report: true);
        }

        CompanySecurityUpdated::dispatch($this->empresaId(), 'usuario', 'updated', $usuario->id_usuario);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleStatus(User $usuario): RedirectResponse
    {
        $this->assertManageableUser($usuario);
        abort_if($usuario->is(auth()->user()), 422, 'No puedes desactivar tu propio usuario desde esta pantalla.');

        $usuario->update(['estado' => ! $usuario->estado]);
        CompanySecurityUpdated::dispatch($this->empresaId(), 'usuario', 'status_changed', $usuario->id_usuario);

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    private function companyRoles()
    {
        return Rol::query()
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
