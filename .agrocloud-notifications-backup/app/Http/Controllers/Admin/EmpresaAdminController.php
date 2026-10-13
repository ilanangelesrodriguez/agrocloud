<?php

namespace App\Http\Controllers\Admin;

use App\Events\CompanySecurityUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Empresa;
use App\Models\User;
use App\Services\CloudinaryService;
use App\Services\Exports\CsvExporter;
use App\Services\Exports\XlsxExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EmpresaAdminController extends Controller
{
    public function index(Request $request)
    {
        if ($request->query()) {
            $data = $request->validate(['q' => ['nullable', 'string', 'max:150'], 'estado' => ['nullable', 'in:0,1'], 'tipo' => ['nullable', 'string', 'max:80']]);
            $request->session()->put('admin.empresas.filters', $data);
            return redirect()->route('admin.empresas.index');
        }
        return view('admin.empresas.index');
    }

    public function listingData(Request $request): array
    {
        $empresas = $this->filteredQuery($request)
            ->withCount([
                'usuarios',
                'usuarios as administradores_count' => fn (Builder $query) => $query
                    ->whereHas('rol', fn (Builder $roleQuery) => $roleQuery->where('codigo', 'ADMIN')),
            ])
            ->orderBy('nombre_comercial')
            ->paginate(12)
            ;

        $tipos = Empresa::query()
            ->whereNotNull('tipo_empresa')
            ->where('tipo_empresa', '!=', '')
            ->distinct()
            ->orderBy('tipo_empresa')
            ->pluck('tipo_empresa');

        $stats = [
            'total' => Empresa::query()->count(),
            'activas' => Empresa::query()->where('estado', true)->count(),
            'inactivas' => Empresa::query()->where('estado', false)->count(),
            'usuarios' => User::query()->whereNotNull('id_empresa')->count(),
        ];

        return compact('empresas', 'tipos', 'stats');
    }

    public function create(): View
    {
        return view('admin.empresas.create');
    }

    public function store(StoreCompanyRequest $request, CloudinaryService $cloudinary): RedirectResponse
    {
        $data = $request->safe()->except(['logo']);
        $data = app(\App\Services\MaestroCatalog::class)->validate($data, ['tipo_empresa' => 'tipo_empresa', 'rubro' => 'rubro', 'grupo_empresarial' => 'grupo'], null, isset($empresa) ? $empresa->toArray() : []);
        $data['estado'] = $request->boolean('estado');
        $upload = null;

        try {
            if ($request->hasFile('logo')) {
                $upload = $cloudinary->uploadImage($request->file('logo'), 'agrocloud/empresas');
                $data['logo_url'] = $upload['url'];
                $data['logo_public_id'] = $upload['public_id'];
            }

            $empresa = Empresa::query()->create($data);
        } catch (Throwable $e) {
            if ($upload) {
                rescue(fn () => $cloudinary->deleteImage($upload['public_id']), report: true);
            }

            report($e);

            return back()->withInput()->withErrors([
                'logo' => 'No se pudo registrar la empresa. Si seleccionaste un logo, revisa Cloudinary.',
            ]);
        }

        CompanySecurityUpdated::dispatch($empresa->id_empresa, 'empresa', 'created', $empresa->id_empresa);

        return redirect()
            ->route('admin.empresas.show', $empresa)
            ->with('success', 'Empresa registrada correctamente. Ahora puedes crear su administrador y demás usuarios.');
    }

    public function show(Empresa $empresa): View
    {
        $empresa->loadCount([
            'usuarios',
            'usuarios as usuarios_activos_count' => fn (Builder $query) => $query->where('estado', true),
            'usuarios as administradores_count' => fn (Builder $query) => $query
                ->whereHas('rol', fn (Builder $roleQuery) => $roleQuery->where('codigo', 'ADMIN')),
        ]);

        $ultimosUsuarios = $empresa->usuarios()
            ->with('rol')
            ->where(function (Builder $query) {
                $query->whereDoesntHave('rol')
                    ->orWhereHas('rol', fn (Builder $roleQuery) => $roleQuery->where('codigo', '!=', 'SUPERADMIN'));
            })
            ->latest('id_usuario')
            ->limit(6)
            ->get();

        return view('admin.empresas.show', compact('empresa', 'ultimosUsuarios'));
    }

    public function edit(Empresa $empresa): View
    {
        return view('admin.empresas.edit', compact('empresa'));
    }

    public function update(
        UpdateCompanyRequest $request,
        Empresa $empresa,
        CloudinaryService $cloudinary,
    ): RedirectResponse {
        $data = $request->safe()->except(['logo']);
        $data = app(\App\Services\MaestroCatalog::class)->validate($data, ['tipo_empresa' => 'tipo_empresa', 'rubro' => 'rubro', 'grupo_empresarial' => 'grupo'], null, isset($empresa) ? $empresa->toArray() : []);
        $data['estado'] = $request->boolean('estado');
        $oldPublicId = $empresa->logo_public_id;
        $upload = null;

        try {
            if ($request->hasFile('logo')) {
                $upload = $cloudinary->uploadImage($request->file('logo'), 'agrocloud/empresas');
                $data['logo_url'] = $upload['url'];
                $data['logo_public_id'] = $upload['public_id'];
            }

            $empresa->update($data);
        } catch (Throwable $e) {
            if ($upload) {
                rescue(fn () => $cloudinary->deleteImage($upload['public_id']), report: true);
            }

            report($e);

            return back()->withInput()->withErrors([
                'logo' => 'No se pudo actualizar la empresa. Si seleccionaste un logo, revisa Cloudinary.',
            ]);
        }

        if ($upload && $oldPublicId) {
            rescue(fn () => $cloudinary->deleteImage($oldPublicId), report: true);
        }

        CompanySecurityUpdated::dispatch($empresa->id_empresa, 'empresa', 'updated', $empresa->id_empresa);

        return redirect()->route('admin.empresas.show', $empresa)->with('success', 'Empresa actualizada correctamente.');
    }

    public function toggleStatus(Empresa $empresa): RedirectResponse
    {
        $empresa->update(['estado' => ! $empresa->estado]);
        CompanySecurityUpdated::dispatch($empresa->id_empresa, 'empresa', 'status_changed', $empresa->id_empresa);

        return back()->with('success', 'Estado de la empresa actualizado.');
    }

    public function exportExcel(Request $request, XlsxExporter $exporter)
    {
        $empresas = $this->filteredQuery($request)
            ->withCount('usuarios')
            ->orderBy('nombre_comercial')
            ->get();

        $rows = $this->exportRows($empresas->all());

        return $exporter->download(
            'empresas_agrocloud_'.now()->format('Ymd_His'),
            'Empresas registradas en AgroCloud AI',
            $this->exportColumns(),
            $rows,
            [
                'Filtros' => $this->filterDescription($request),
                'Registros' => count($rows),
            ],
            'Empresas',
        );
    }

    public function exportCsv(Request $request, CsvExporter $exporter)
    {
        $empresas = $this->filteredQuery($request)
            ->withCount('usuarios')
            ->orderBy('nombre_comercial')
            ->get();

        return $exporter->download(
            'empresas_agrocloud_'.now()->format('Ymd_His'),
            $this->exportColumns(),
            $this->exportRows($empresas->all()),
        );
    }

    public function report(Request $request): View
    {
        $empresas = $this->filteredQuery($request)
            ->withCount('usuarios')
            ->orderBy('nombre_comercial')
            ->get();

        return view('exports.report', [
            'title' => 'Empresas registradas',
            'subtitle' => 'Administración global · AgroCloud AI',
            'columns' => $this->exportColumns(),
            'rows' => $this->exportRows($empresas->all()),
            'meta' => [
                'Filtros' => $this->filterDescription($request),
                'Registros' => $empresas->count(),
            ],
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        if (!$request->query()) $request->query->add(session('admin.empresas.filters', []));
        $search = trim((string) $request->string('q'));
        $estado = $request->string('estado')->toString();
        $tipo = trim((string) $request->string('tipo'));

        return Empresa::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('nombre_comercial', 'like', "%{$search}%")
                        ->orWhere('razon_social', 'like', "%{$search}%")
                        ->orWhere('ruc', 'like', "%{$search}%")
                        ->orWhere('grupo_empresarial', 'like', "%{$search}%");
                });
            })
            ->when(in_array($estado, ['0', '1'], true), fn (Builder $query) => $query->where('estado', $estado === '1'))
            ->when($tipo !== '', fn (Builder $query) => $query->where('tipo_empresa', $tipo));
    }

    /** @return array<int, array{key:string,label:string,width:float,type?:string,status?:bool}> */
    private function exportColumns(): array
    {
        return [
            ['key' => 'ruc', 'label' => 'RUC', 'width' => 15],
            ['key' => 'nombre_comercial', 'label' => 'Nombre comercial', 'width' => 24],
            ['key' => 'razon_social', 'label' => 'Razón social', 'width' => 36],
            ['key' => 'grupo_empresarial', 'label' => 'Grupo empresarial', 'width' => 22],
            ['key' => 'tipo_empresa', 'label' => 'Tipo', 'width' => 20],
            ['key' => 'ubicacion', 'label' => 'Ubicación', 'width' => 28],
            ['key' => 'usuarios', 'label' => 'Usuarios', 'width' => 12, 'type' => 'number'],
            ['key' => 'correo', 'label' => 'Correo', 'width' => 28],
            ['key' => 'telefono', 'label' => 'Teléfono', 'width' => 18],
            ['key' => 'estado', 'label' => 'Estado', 'width' => 13, 'status' => true],
        ];
    }

    /**
     * @param array<int, Empresa> $empresas
     * @return array<int, array<string, mixed>>
     */
    private function exportRows(array $empresas): array
    {
        return array_map(function (Empresa $empresa): array {
            $ubicacion = array_filter([$empresa->distrito, $empresa->provincia, $empresa->departamento]);

            return [
                'ruc' => $empresa->ruc,
                'nombre_comercial' => $empresa->nombre_comercial,
                'razon_social' => $empresa->razon_social,
                'grupo_empresarial' => $empresa->grupo_empresarial ?: '—',
                'tipo_empresa' => $empresa->tipo_empresa ?: '—',
                'ubicacion' => $ubicacion ? implode(', ', $ubicacion) : ($empresa->direccion ?: '—'),
                'usuarios' => (int) ($empresa->usuarios_count ?? 0),
                'correo' => $empresa->correo ?: '—',
                'telefono' => $empresa->telefono ?: '—',
                'estado' => $empresa->estado ? 'Activa' : 'Inactiva',
            ];
        }, $empresas);
    }

    private function filterDescription(Request $request): string
    {
        $parts = [];
        if ($q = trim((string) $request->string('q'))) {
            $parts[] = "Búsqueda: {$q}";
        }
        if (in_array($request->string('estado')->toString(), ['0', '1'], true)) {
            $parts[] = 'Estado: '.($request->string('estado')->toString() === '1' ? 'Activas' : 'Inactivas');
        }
        if ($tipo = trim((string) $request->string('tipo'))) {
            $parts[] = "Tipo: {$tipo}";
        }

        return $parts ? implode(' · ', $parts) : 'Sin filtros';
    }
}
