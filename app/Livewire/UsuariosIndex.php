<?php

namespace App\Livewire;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class UsuariosIndex extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $rol = '';
    public string $cargo = '';
    public string $estado = '';
    public string $orden = 'nombre';
    public string $direccion = 'asc';
    public int $porPagina = 10;

    private function baseQuery(): Builder
    {
        $user = auth()->user();
        abort_unless($user && $user->id_empresa && $user->hasPermission('usuarios.ver'), 403);

        return User::query()->where('id_empresa', $user->id_empresa)
            ->where(fn ($query) => $query->whereDoesntHave('rol')->orWhereHas('rol', fn ($role) => $role->where('codigo', '!=', 'SUPERADMIN')));
    }

    public function updated($property): void
    {
        if (in_array($property, ['buscar', 'rol', 'cargo', 'estado', 'porPagina'], true)) $this->resetPage();
    }

    public function limpiarBusqueda(): void
    {
        $this->reset('buscar', 'rol', 'cargo', 'estado');
        $this->resetPage();
    }

    public function ordenar(string $column): void
    {
        abort_unless(in_array($column, ['nombre', 'rol', 'estado'], true), 422);
        $this->direccion = $this->orden === $column && $this->direccion === 'asc' ? 'desc' : 'asc';
        $this->orden = $column;
        $this->resetPage();
    }

    #[On('users-saved')]
    public function refreshUsers(): void {}

    public function render()
    {
        $base = $this->baseQuery();
        $resumen = ['total' => (clone $base)->count(), 'activos' => (clone $base)->where('estado', true)->count(), 'inactivos' => (clone $base)->where('estado', false)->count()];
        $roles = Rol::query()->where('codigo', '!=', 'SUPERADMIN')->with(['permisos' => fn ($q) => $q->where('permiso.estado', true)])->orderBy('nombre')->get();
        $cargos = app(\App\Services\MaestroCatalog::class)->options('cargo', auth()->user()->id_empresa)->pluck('nombre');
        $term = mb_substr(trim($this->buscar), 0, 150);
        $query = (clone $base)->with('rol')
            ->when($term !== '', fn ($q) => $q->where(fn ($inner) => $inner->whereLike('nombre', '%'.$term.'%')->orWhereLike('apellido', '%'.$term.'%')->orWhereLike('correo', '%'.$term.'%')->orWhereLike('cargo', '%'.$term.'%')))
            ->when($this->rol !== '', fn ($q) => ctype_digit($this->rol) ? $q->where('id_rol', (int) $this->rol) : $q->whereRaw('1 = 0'))
            ->when($this->cargo !== '', fn ($q) => $q->where('cargo', $this->cargo))
            ->when(in_array($this->estado, ['1', '0'], true), fn ($q) => $q->where('estado', $this->estado === '1'));
        $direction = $this->direccion === 'desc' ? 'desc' : 'asc';
        if ($this->orden === 'rol') {
            $query->orderBy(Rol::select('nombre')->whereColumn('rol.id_rol', 'usuario.id_rol')->limit(1), $direction);
        } else {
            $query->orderBy($this->orden === 'estado' ? 'estado' : 'nombre', $direction);
        }
        $usuarios = $query->orderBy('apellido')->orderBy('id_usuario')->paginate(in_array($this->porPagina, [10, 25, 50], true) ? $this->porPagina : 10);

        return view('usuarios.index', compact('usuarios', 'resumen', 'roles', 'cargos'))->layout('components.layouts.app', ['title' => 'Usuarios']);
    }
}
