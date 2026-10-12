<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UsuariosIndex extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $filtro = '';

    public function buscarUsuarios(): void
    {
        $this->buscar = trim($this->buscar);
        $this->validate(['buscar' => ['required', 'string', 'max:150']], [
            'buscar.required' => 'Escribe un término para buscar.',
            'buscar.max' => 'La búsqueda no puede superar los 150 caracteres.',
        ]);
        $this->filtro = $this->buscar;
        $this->resetPage();
    }

    public function limpiarBusqueda(): void
    {
        $this->resetValidation('buscar');
        $this->buscar = '';
        $this->filtro = '';
        $this->resetPage();
    }

    public function render()
    {
        $usuarios = User::query()
            ->with('rol')
            ->where('id_empresa', auth()->user()->id_empresa)
            ->where(function ($query) {
                $query->whereDoesntHave('rol')
                    ->orWhereHas('rol', fn ($roleQuery) => $roleQuery->where('codigo', '!=', 'SUPERADMIN'));
            })
            ->when($this->filtro !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('nombre', 'like', '%'.$this->filtro.'%')
                        ->orWhere('apellido', 'like', '%'.$this->filtro.'%')
                        ->orWhere('correo', 'like', '%'.$this->filtro.'%')
                        ->orWhere('cargo', 'like', '%'.$this->filtro.'%');
                });
            })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(12);

        return view('usuarios.index', compact('usuarios'))
            ->layout('components.layouts.app', ['title' => 'Usuarios']);
    }
}
