<?php

namespace App\Livewire;

use App\Models\Productor;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class ProductoresIndex extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $filtro = '';
    public string $departamento = '';
    public string $provincia = '';
    public bool $incompletos = false;
    public int $porPagina = 15;

    public function updated($property): void
    {
        $this->validate(['buscar' => 'nullable|string|max:150', 'departamento' => 'nullable|string|max:100', 'provincia' => 'nullable|string|max:100', 'porPagina' => 'in:15,30,50']);
        if ($property === 'buscar') $this->filtro = trim($this->buscar);
        if ($property === 'departamento') $this->provincia = '';
        $this->resetPage();
    }

    #[On('productores-actualizados')]
    public function actualizar(): void {}

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
        $this->reset('departamento', 'provincia', 'incompletos');
        $this->resetValidation();
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
            ->when($this->departamento !== '', fn ($q) => $q->where('departamento', $this->departamento))
            ->when($this->provincia !== '', fn ($q) => $q->where('provincia', $this->provincia))
            ->when($this->incompletos, function ($q) {
                $q->where(function ($location) {
                    foreach (['departamento', 'provincia', 'distrito', 'ubigeo'] as $field) $location->orWhereNull($field)->orWhere($field, '');
                });
            })
            ->orderBy('nombre_razon_social');
        $query = $productores;
        $perPage = in_array($this->porPagina, [15,30,50], true) ? $this->porPagina : 15;
        $productores = (clone $query)->paginate($perPage);
        if ($productores->currentPage() > $productores->lastPage()) {
            $this->setPage($productores->lastPage());
            $productores = $query->paginate($perPage);
        }
        $departamentos = Productor::forCurrentCompany()->whereNotNull('departamento')->where('departamento', '!=', '')->distinct()->orderBy('departamento')->pluck('departamento');
        $provincias = Productor::forCurrentCompany()->where('departamento', $this->departamento)->whereNotNull('provincia')->where('provincia', '!=', '')->distinct()->orderBy('provincia')->pluck('provincia');

        return view('productores.index', compact('productores', 'departamentos', 'provincias'))
            ->layout('components.layouts.app', ['title' => 'Productores']);
    }
}
