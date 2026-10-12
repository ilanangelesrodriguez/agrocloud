<?php

namespace App\Livewire;

use App\Models\Permiso;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class PermisosIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public function render()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
        $permisos = Permiso::query()->withCount('roles')->orderBy('modulo')->orderBy('nombre')->paginate(20);
        return view('livewire.permisos-index', compact('permisos'));
    }
}
