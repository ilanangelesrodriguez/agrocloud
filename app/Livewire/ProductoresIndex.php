<?php

namespace App\Livewire;

use App\Models\Productor;
use Livewire\Component;
use Livewire\WithPagination;

class ProductoresIndex extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $filtro = '';

    public function buscarProductores(): void
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
        $productores = Productor::query()
            ->forCurrentCompany()
            ->when($this->filtro !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('ruc', 'ilike', '%'.$this->filtro.'%')
                        ->orWhere('nombre_razon_social', 'ilike', '%'.$this->filtro.'%')
                        ->orWhere('fundo', 'ilike', '%'.$this->filtro.'%')
                        ->orWhere('ubicacion', 'ilike', '%'.$this->filtro.'%')
                        ->orWhere('departamento', 'ilike', '%'.$this->filtro.'%')
                        ->orWhere('provincia', 'ilike', '%'.$this->filtro.'%')
                        ->orWhere('distrito', 'ilike', '%'.$this->filtro.'%')
                        ->orWhere('ubigeo', 'ilike', '%'.$this->filtro.'%');
                });
            })
            ->orderBy('nombre_razon_social')
            ->paginate(10);

        return view('productores.index', compact('productores'))
            ->layout('components.layouts.app', ['title' => 'Productores']);
    }
}
