<?php

namespace App\Livewire;

use App\Models\Lote;
use Livewire\Component;
use Livewire\WithPagination;

class LotesIndex extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $filtro = '';

    public function buscarLotes(): void
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
        $lotes = Lote::query()
            ->forCurrentCompany()
            ->with('productor')
            ->withExists(['recepcion', 'muestreos'])
            ->when($this->filtro !== '', function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where('codigo_lote', 'ilike', '%'.$this->filtro.'%')
                        ->orWhere('producto', 'ilike', '%'.$this->filtro.'%')
                        ->orWhere('variedad', 'ilike', '%'.$this->filtro.'%')
                        ->orWhereHas('productor', function ($productorQuery) {
                            $productorQuery
                                ->where('nombre_razon_social', 'ilike', '%'.$this->filtro.'%')
                                ->orWhere('ruc', 'ilike', '%'.$this->filtro.'%');
                        });
                });
            })
            ->orderByDesc('fecha_recepcion')
            ->orderByDesc('id_lote')
            ->paginate(10);

        return view('livewire.lotes-index', compact('lotes'))
            ->layout('components.layouts.app', ['title' => 'Lotes']);
    }
}
