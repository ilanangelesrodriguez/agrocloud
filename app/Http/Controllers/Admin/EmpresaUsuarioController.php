<?php

namespace App\Http\Controllers\Admin;

use App\Events\CompanySecurityUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use App\Services\CloudinaryService;
use App\Services\Exports\CsvExporter;
use App\Services\Exports\XlsxExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class EmpresaUsuarioController extends Controller
{
    public function index(Request $request, Empresa $empresa): View
    {
        $usuarios = $this->filteredQuery($request, $empresa)
            ->with('rol')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(15)
            ->withQueryString();

        $roles = $this->companyRoles();
        $stats = [
            'total' => $this->companyUsers($empresa)->count(),
            'activos' => $this->companyUsers($empresa)->where('estado', true)->count(),
            'inactivos' => $this->companyUsers($empresa)->where('estado', false)->count(),
            'admins' => $this->companyUsers($empresa)
                ->whereHas('rol', fn (Builder $query) => $query->where('codigo', 'ADMIN'))
                ->count(),
        ];

        return view('admin.empresas.usuarios.index', compact('empresa', 'usuarios', 'roles', 'stats'));
    }

    public function create(Empresa $empresa): View
    {
        $roles = $this->companyRoles();

        return view('admin.empresas.usuarios.create', compact('empresa', 'roles'));
    }

    public function store(
        StoreUserRequest $request,
        Empresa $empresa,
        CloudinaryService $cloudinary,
    ): RedirectResponse {
        $data = $request->safe()->except(['password', 'password_confirmation', 'foto_perfil']);
        $data['id_empresa'] = $empresa->id_empresa;
        $data['password_hash'] = $request->string('password')->toString();
        $data['estado'] = $request->boolean('estado');
        $upload = null;

        try {
            if ($request->hasFile('foto_perfil')) {
                $upload = $cloudinary->uploadImage($request->file('foto_perfil'), 'agrocloud/perfiles');
                $data['foto_perfil_url'] = $upload['url'];
                $data['foto_perfil_public_id'] = $upload['public_id'];
            }

            $usuario = DB::transaction(fn () => User::query()->create($data));
        } catch (Throwable $e) {
            if ($upload) {
                rescue(fn () => $cloudinary->deleteImage($upload['public_id']), report: true);
            }

            report($e);

            return back()->withInput()->withErrors([
                'foto_perfil' => 'No se pudo crear el usuario. Si seleccionaste una foto, revisa Cloudinary.',
            ]);
        }

        CompanySecurityUpdated::dispatch($empresa->id_empresa, 'usuario', 'created', $usuario->id_usuario);

        return redirect()
            ->route('admin.empresas.usuarios.index', $empresa)
            ->with('success', 'Usuario creado para '.$empresa->nombre_comercial.'.');
    }

    public function edit(Empresa $empresa, User $usuario): View
    {
        $this->assertBelongsToCompany($empresa, $usuario);
        $roles = $this->companyRoles();

        return view('admin.empresas.usuarios.edit', compact('empresa', 'usuario', 'roles'));
    }

    public function update(
        UpdateUserRequest $request,
        Empresa $empresa,
        User $usuario,
        CloudinaryService $cloudinary,
    ): RedirectResponse {
        $this->assertBelongsToCompany($empresa, $usuario);
        $data = $request->safe()->except(['password', 'password_confirmation', 'foto_perfil']);
        $data['estado'] = $request->boolean('estado');

        if ($request->filled('password')) {
            $data['password_hash'] = $request->string('password')->toString();
        }

        $oldPublicId = $usuario->foto_perfil_public_id;
        $upload = null;

        try {
            if ($request->hasFile('foto_perfil')) {
                $upload = $cloudinary->uploadImage($request->file('foto_perfil'), 'agrocloud/perfiles');
                $data['foto_perfil_url'] = $upload['url'];
                $data['foto_perfil_public_id'] = $upload['public_id'];
            }

            DB::transaction(fn () => $usuario->update($data));
        } catch (Throwable $e) {
            if ($upload) {
                rescue(fn () => $cloudinary->deleteImage($upload['public_id']), report: true);
            }

            report($e);

            return back()->withInput()->withErrors([
                'foto_perfil' => 'No se pudo actualizar el usuario. Si seleccionaste una foto, revisa Cloudinary.',
            ]);
        }

        if ($upload && $oldPublicId) {
            rescue(fn () => $cloudinary->deleteImage($oldPublicId), report: true);
        }

        CompanySecurityUpdated::dispatch($empresa->id_empresa, 'usuario', 'updated', $usuario->id_usuario);

        return redirect()
            ->route('admin.empresas.usuarios.index', $empresa)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleStatus(Empresa $empresa, User $usuario): RedirectResponse
    {
        $this->assertBelongsToCompany($empresa, $usuario);
        $usuario->update(['estado' => ! $usuario->estado]);
        CompanySecurityUpdated::dispatch($empresa->id_empresa, 'usuario', 'status_changed', $usuario->id_usuario);

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function exportExcel(Request $request, Empresa $empresa, XlsxExporter $exporter)
    {
        $usuarios = $this->filteredQuery($request, $empresa)
            ->with('rol')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();
        $rows = $this->exportRows($usuarios->all());

        return $exporter->download(
            'usuarios_'.$this->slug($empresa->nombre_comercial).'_'.now()->format('Ymd_His'),
            'Usuarios de '.$empresa->nombre_comercial,
            $this->exportColumns(),
            $rows,
            [
                'RUC' => $empresa->ruc,
                'Filtros' => $this->filterDescription($request),
                'Registros' => count($rows),
            ],
            'Usuarios',
        );
    }

    public function exportCsv(Request $request, Empresa $empresa, CsvExporter $exporter)
    {
        $usuarios = $this->filteredQuery($request, $empresa)
            ->with('rol')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        return $exporter->download(
            'usuarios_'.$this->slug($empresa->nombre_comercial).'_'.now()->format('Ymd_His'),
            $this->exportColumns(),
            $this->exportRows($usuarios->all()),
        );
    }

    public function report(Request $request, Empresa $empresa): View
    {
        $usuarios = $this->filteredQuery($request, $empresa)
            ->with('rol')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        return view('exports.report', [
            'title' => 'Usuarios · '.$empresa->nombre_comercial,
            'subtitle' => $empresa->razon_social.' · RUC '.$empresa->ruc,
            'columns' => $this->exportColumns(),
            'rows' => $this->exportRows($usuarios->all()),
            'meta' => [
                'Filtros' => $this->filterDescription($request),
                'Registros' => $usuarios->count(),
            ],
            'logoUrl' => $empresa->logo_url,
        ]);
    }

    private function filteredQuery(Request $request, Empresa $empresa): Builder
    {
        $search = trim((string) $request->string('q'));
        $estado = $request->string('estado')->toString();
        $rolId = $request->integer('rol');

        return $this->companyUsers($empresa)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%")
                        ->orWhere('cargo', 'like', "%{$search}%");
                });
            })
            ->when(in_array($estado, ['0', '1'], true), fn (Builder $query) => $query->where('estado', $estado === '1'))
            ->when($rolId > 0, fn (Builder $query) => $query->where('id_rol', $rolId));
    }

    private function companyUsers(Empresa $empresa): Builder
    {
        return User::query()
            ->where('id_empresa', $empresa->id_empresa)
            ->where(function (Builder $query) {
                $query->whereDoesntHave('rol')
                    ->orWhereHas('rol', fn (Builder $roleQuery) => $roleQuery->where('codigo', '!=', 'SUPERADMIN'));
            });
    }

    private function companyRoles()
    {
        return Rol::query()
            ->where('estado', true)
            ->where('codigo', '!=', 'SUPERADMIN')
            ->orderBy('nombre')
            ->get();
    }

    private function assertBelongsToCompany(Empresa $empresa, User $usuario): void
    {
        abort_unless((int) $usuario->id_empresa === (int) $empresa->id_empresa, 404);
        $usuario->loadMissing('rol');
        abort_if($usuario->rol?->codigo === 'SUPERADMIN', 404);
    }

    /** @return array<int, array{key:string,label:string,width:float,type?:string,status?:bool}> */
    private function exportColumns(): array
    {
        return [
            ['key' => 'nombre', 'label' => 'Nombre completo', 'width' => 27],
            ['key' => 'correo', 'label' => 'Correo', 'width' => 31],
            ['key' => 'rol', 'label' => 'Rol', 'width' => 28],
            ['key' => 'cargo', 'label' => 'Cargo', 'width' => 28],
            ['key' => 'telefono', 'label' => 'Teléfono', 'width' => 18],
            ['key' => 'ultimo_acceso', 'label' => 'Último acceso', 'width' => 20],
            ['key' => 'estado', 'label' => 'Estado', 'width' => 13, 'status' => true],
        ];
    }

    /**
     * @param array<int, User> $usuarios
     * @return array<int, array<string, mixed>>
     */
    private function exportRows(array $usuarios): array
    {
        return array_map(fn (User $usuario): array => [
            'nombre' => $usuario->nombre_completo,
            'correo' => $usuario->correo,
            'rol' => $usuario->rol?->nombre ?? 'Sin rol',
            'cargo' => $usuario->cargo ?: '—',
            'telefono' => $usuario->telefono ?: '—',
            'ultimo_acceso' => $usuario->ultimo_acceso?->format('d/m/Y H:i') ?? 'Sin acceso registrado',
            'estado' => $usuario->estado ? 'Activo' : 'Inactivo',
        ], $usuarios);
    }

    private function filterDescription(Request $request): string
    {
        $parts = [];
        if ($q = trim((string) $request->string('q'))) {
            $parts[] = "Búsqueda: {$q}";
        }
        if (in_array($request->string('estado')->toString(), ['0', '1'], true)) {
            $parts[] = 'Estado: '.($request->string('estado')->toString() === '1' ? 'Activos' : 'Inactivos');
        }
        if ($request->integer('rol') > 0) {
            $roleName = Rol::query()->whereKey($request->integer('rol'))->value('nombre');
            if ($roleName) {
                $parts[] = "Rol: {$roleName}";
            }
        }

        return $parts ? implode(' · ', $parts) : 'Sin filtros';
    }

    private function slug(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/i', '_', $value) ?: 'empresa', '_');
    }
}
