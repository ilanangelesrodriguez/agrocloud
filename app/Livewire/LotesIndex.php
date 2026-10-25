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
    public string $estado = '';
    public string $producto = '';
    public string $desde = '';
    public string $hasta = '';
    public int $porPagina = 15;

    public function updated($property): void
    {
        $this->validate(['estado' => 'nullable|string|max:40', 'producto' => 'nullable|string|max:150', 'desde' => 'nullable|date_format:Y-m-d', 'hasta' => array_filter(['nullable', 'date_format:Y-m-d', $this->desde !== '' ? 'after_or_equal:desde' : null]), 'porPagina' => 'in:15,30,50']);
        $this->resetPage();
    }

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
        $this->reset('estado', 'producto', 'desde', 'hasta');
        $this->resetValidation();
        $this->buscar = '';
        $this->filtro = '';
        $this->resetPage();
    }

    public function anular(int $id): void
    {
        app(\App\Services\AnularLote::class)->ejecutar($id);
        session()->flash('success', 'Lote anulado. Se conserva su historial.');
    }

    public function render()
    {
        $lotes = Lote::query()
            ->forCurrentCompany()
            ->with(['productor', 'recepcion'])
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
            ->when($this->estado !== '', fn ($q) => $q->where('estado', $this->estado))
            ->when($this->producto !== '', fn ($q) => $q->where('producto', $this->producto))
            ->when(preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->desde), fn ($q) => $q->whereDate('fecha_recepcion', '>=', $this->desde))
            ->when(preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->hasta), fn ($q) => $q->whereDate('fecha_recepcion', '<=', $this->hasta))
            ->orderByDesc('fecha_recepcion')
            ->orderByDesc('id_lote')
            ->paginate(in_array($this->porPagina, [15,30,50], true) ? $this->porPagina : 15);

        $estados = collect(\App\Enums\LoteEstado::cases())->mapWithKeys(fn ($state) => [$state->value => $state->label()])->all();
        $productos = app(\App\Services\MaestroCatalog::class)->options('producto', auth()->user()->id_empresa)->pluck('nombre');
        return view('livewire.lotes-index', compact('lotes', 'estados', 'productos'))
            ->layout('components.layouts.app', ['title' => 'Lotes']);
    }
}
