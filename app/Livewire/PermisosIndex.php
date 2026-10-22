<?php

namespace App\Livewire;

use App\Models\Permiso;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class PermisosIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $buscar = '';
    public string $modulo = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
        $this->buscar = session('permisos.buscar', '');
        $this->modulo = session('permisos.modulo', '');
    }

    public function updated(): void
    {
        $this->validate(['buscar' => 'nullable|string|max:150', 'modulo' => 'nullable|string|max:100']);
        session(['permisos.buscar' => $this->buscar, 'permisos.modulo' => $this->modulo]);
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->reset('buscar', 'modulo');
        $this->updated();
    }

    public function render()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
        $permisos = Permiso::query()->with('roles')->withCount('roles')->when($this->modulo !== '', fn ($q) => $q->where('modulo', $this->modulo))->when(trim($this->buscar) !== '', fn ($q) => $q->where(fn ($w) => $w->whereLike('nombre', '%'.trim($this->buscar).'%')->orWhereLike('codigo', '%'.trim($this->buscar).'%')))->orderBy('modulo')->orderBy('nombre')->paginate(20);
        $modulos = Permiso::query()->distinct()->orderBy('modulo')->pluck('modulo');
        return view('livewire.permisos-index', compact('permisos', 'modulos'));
    }
}
