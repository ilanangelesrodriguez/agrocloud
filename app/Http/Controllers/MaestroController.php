<?php

namespace App\Http\Controllers;

use App\Models\MaestroValor;
use App\Services\MaestroCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class MaestroController extends Controller
{
    private function definition(string $type): array
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (auth()->user()?->isCompanyAdmin() && auth()->user()->id_empresa), 403);
        $definition = config('maestros')[$type] ?? null;
        abort_unless(is_array($definition), 404);
        return $definition;
    }

    public function index()
    {
        $this->definition('cargo');
        return view('maestros.index', ['definitions' => config('maestros')]);
    }

    public function show(Request $request, string $tipo)
    {
        $definition = $this->definition($tipo);
        if ($request->query()) {
            $data = $request->validate(['q' => ['nullable', 'string', 'max:150']]);
            $request->session()->put('maestros.'.auth()->id().'.'.$tipo.'.filters', $data);
            return redirect()->route('maestros.show', $tipo);
        }
        return view('maestros.show', compact('tipo', 'definition'));
    }

    public function listingData(Request $request, string $tipo): array
    {
        $definition = $this->definition($tipo);
        $request->validate(['q' => ['nullable', 'string', 'max:150']]);
        if ($tipo === 'ubigeo') {
            $catalog = fn ($file, $key) => collect(json_decode(file_get_contents(public_path('data/ubigeo/'.$file.'.json')), true)[$key]);
            $departments = $catalog('1_ubigeo_departamentos', 'ubigeo_departamentos')->keyBy('id');
            $provinces = $catalog('2_ubigeo_provincias', 'ubigeo_provincias')->keyBy('id');
            $rows = $catalog('3_ubigeo_distritos', 'ubigeo_distritos')->map(function ($row) use ($provinces, $departments) {
                $province = $provinces[$row['provincia_id']];
                return $row + ['provincia' => $province['provincia'], 'departamento' => $departments[$province['departamento_id']]['departamento']];
            })->filter(fn ($row) => !$request->filled('q') || str_contains(mb_strtolower(implode(' ', $row)), mb_strtolower(trim($request->string('q')))))->values();
            $items = new LengthAwarePaginator($rows->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), 20), $rows->count(), 20, \Illuminate\Pagination\Paginator::resolveCurrentPage(), ['path' => $request->url(), 'query' => $request->query()]);
            return compact('items', 'definition', 'tipo');
        }
        $items = MaestroValor::query()->visibleTo($tipo, auth()->user()->id_empresa)->with('parent')
            ->when($request->filled('q'), fn ($q) => $q->whereRaw('LOWER(nombre) LIKE ?', ['%'.mb_strtolower(trim($request->string('q'))).'%']))
            ->orderBy('nombre')->paginate(20);
        $disabled = DB::table('maestro_empresa_config')->where('id_empresa', auth()->user()->id_empresa)->where('estado', false)->pluck('maestro_id')->all();
        return compact('items', 'definition', 'tipo', 'disabled');
    }

    private function company(string $type): ?int
    {
        $definition = $this->definition($type);
        abort_if($definition['scope'] === 'readonly', 403);
        if ($definition['scope'] === 'company') {
            abort_unless(auth()->user()->isCompanyAdmin(), 403, 'Este catálogo se administra desde una empresa.');
            return auth()->user()->id_empresa;
        }
        abort_unless(auth()->user()->isSuperAdmin() || $definition['scope'] === 'shared', 403);
        return auth()->user()->isSuperAdmin() ? null : auth()->user()->id_empresa;
    }

    private function editable(string $type, MaestroValor $item): void
    {
        abort_unless($item->tipo === $type && $item->id_empresa === $this->company($type), 403);
    }

    public function create(string $tipo)
    {
        $this->company($tipo);
        return $this->form($tipo);
    }

    public function edit(string $tipo, MaestroValor $item)
    {
        $this->editable($tipo, $item);
        return $this->form($tipo, $item);
    }

    private function form(string $tipo, ?MaestroValor $item = null)
    {
        $definition = $this->definition($tipo);
        $parents = isset($definition['parent']) ? app(MaestroCatalog::class)->options($definition['parent']) : collect();
        return view('maestros.form', compact('tipo', 'definition', 'item', 'parents'));
    }

    private function data(Request $request, string $tipo, ?MaestroValor $item = null): array
    {
        $data = $request->validate(['nombre' => ['required', 'string', 'max:150'], 'descripcion' => ['nullable', 'string', 'max:1000'], 'parent_id' => ['nullable', 'integer'], 'codigo' => ['nullable', 'string', 'max:40']]);
        $data['nombre'] = trim($data['nombre']);
        if ($data['nombre'] === '') throw ValidationException::withMessages(['nombre' => 'Escribe un nombre.']);
        $limit = ['tipo_empresa' => 80, 'metodo' => 80, 'categoria' => 100, 'unidad' => 40][$tipo] ?? 150;
        if (mb_strlen($data['nombre']) > $limit) throw ValidationException::withMessages(['nombre' => 'El nombre de este catálogo admite hasta '.$limit.' caracteres.']);
        if ($tipo === 'unidad' && empty(trim($data['codigo'] ?? ''))) throw ValidationException::withMessages(['codigo' => 'Escribe el código de la unidad.']);
        $data['id_empresa'] = $this->company($tipo);
        $data['tipo'] = $tipo;
        $data['parent_id'] = $tipo === 'variedad' && isset($data['parent_id']) ? (int) $data['parent_id'] : null;
        if ($tipo === 'variedad') {
            abort_unless(MaestroValor::query()->where('id', $data['parent_id'])->where('tipo', 'producto')->whereNull('id_empresa')->where('estado', true)->exists(), 422, 'Selecciona un producto activo.');
        }
        $data['clave'] = hash('sha256', $tipo.'|'.($data['id_empresa'] ?? 'global').'|'.($data['parent_id'] ?? '').'|'.mb_strtolower($data['nombre']));
        if (MaestroValor::query()->where('clave', $data['clave'])->when($item, fn ($q) => $q->where('id', '!=', $item->id))->exists()) throw ValidationException::withMessages(['nombre' => 'Esta opción ya existe.']);
        if ($item && app(MaestroCatalog::class)->isUsed($item) && ($item->nombre !== $data['nombre'] || $item->parent_id !== $data['parent_id'] || ($tipo === 'unidad' && $item->codigo !== ($data['codigo'] ?? null)))) {
            throw ValidationException::withMessages(['nombre' => 'La opción está en uso. Puedes editar su descripción o desactivarla; para otro nombre crea una opción nueva.']);
        }
        if ($tipo === 'unidad' && MaestroValor::query()->where('tipo', 'unidad')->where('codigo', $data['codigo'] ?? null)->when($item, fn ($q) => $q->where('id', '!=', $item->id))->exists()) throw ValidationException::withMessages(['codigo' => 'El código de unidad ya existe.']);
        return $data;
    }

    public function store(Request $request, string $tipo)
    {
        MaestroValor::create($this->data($request, $tipo));
        return redirect()->route('maestros.show', $tipo)->with('success', 'Opción registrada correctamente.');
    }

    public function storeOption(Request $request, string $tipo)
    {
        $company = $this->company($tipo);
        $request->validate(['nombre' => ['required', 'string', 'max:150'], 'parent_name' => ['nullable', 'string', 'max:150']]);
        if (app(MaestroCatalog::class)->options($tipo, $company)->contains(fn ($item) => mb_strtolower($item->nombre) === mb_strtolower(trim((string) $request->input('nombre')))
            && ($tipo !== 'variedad' || mb_strtolower($item->parent?->nombre ?? '') === mb_strtolower((string) $request->input('parent_name'))))) {
            throw ValidationException::withMessages(['nombre' => 'Esta opción ya está disponible. Selecciónala en la lista.']);
        }
        if ($tipo === 'variedad') {
            $parent = MaestroValor::query()->where('tipo', 'producto')->whereNull('id_empresa')->where('estado', true)
                ->whereRaw('LOWER(nombre) = ?', [mb_strtolower(trim((string) $request->input('parent_name')))])->first();
            if (!$parent) throw ValidationException::withMessages(['parent_name' => 'Primero selecciona un producto activo.']);
            $request->merge(['parent_id' => $parent->id]);
        }
        $item = MaestroValor::create($this->data($request, $tipo));
        return response()->json(['option' => ['id' => $item->id, 'nombre' => $item->nombre, 'parent' => $item->parent?->nombre, 'codigo' => $item->codigo]], 201);
    }

    public function update(Request $request, string $tipo, MaestroValor $item)
    {
        $this->editable($tipo, $item);
        $item->update($this->data($request, $tipo, $item));
        return redirect()->route('maestros.show', $tipo)->with('success', 'Opción actualizada correctamente.');
    }

    public function toggle(string $tipo, MaestroValor $item)
    {
        $definition = $this->definition($tipo);
        abort_unless($item->tipo === $tipo, 404);
        if ($definition['scope'] === 'shared' && !$item->id_empresa && auth()->user()->isCompanyAdmin()) {
            abort_unless($item->estado, 422, 'Esta opción está desactivada en el catálogo global.');
            $current = DB::table('maestro_empresa_config')->where('id_empresa', auth()->user()->id_empresa)->where('maestro_id', $item->id)->value('estado');
            DB::table('maestro_empresa_config')->updateOrInsert(['id_empresa' => auth()->user()->id_empresa, 'maestro_id' => $item->id], ['estado' => $current === null ? false : !$current]);
        } else {
            $this->editable($tipo, $item);
            $item->update(['estado' => !$item->estado]);
        }
        return back()->with('success', 'Disponibilidad actualizada. Los registros anteriores se conservan.');
    }
}
