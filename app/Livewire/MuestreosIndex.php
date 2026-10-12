<?php

namespace App\Livewire;

use App\Models\Muestreo;
use Livewire\Component;
use Livewire\WithPagination;

class MuestreosIndex extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $filtro = '';

    public function buscarMuestreos(): void
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
        $muestreos = Muestreo::query()->forCurrentCompany()
            ->with(['lote.productor', 'inspector'])
            ->withCount('inspecciones')
            ->when($this->filtro !== '', fn ($query) => $query->whereHas(
                'lote',
                fn ($lote) => $lote->where('codigo_lote', 'ilike', '%'.$this->filtro.'%')
            ))
            ->latest('id_muestreo')
            ->paginate(12);

        return view('livewire.muestreos-index', compact('muestreos'))
            ->layout('components.layouts.app', ['title' => 'Muestreos']);
    }
}
