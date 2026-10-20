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
        $this->reset('estado', 'producto', 'desde', 'hasta');
        $this->resetValidation();
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
            ->when($this->estado !== '', fn ($q) => $q->where('estado', $this->estado))
            ->when($this->producto !== '', fn ($q) => $q->whereHas('lote', fn ($l) => $l->where('producto', $this->producto)))
            ->when(preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->desde), fn ($q) => $q->whereDate('created_at', '>=', $this->desde))
            ->when(preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->hasta), fn ($q) => $q->whereDate('created_at', '<=', $this->hasta))
            ->latest('id_muestreo')
            ->paginate(in_array($this->porPagina, [15,30,50], true) ? $this->porPagina : 15);

        $estados = ['REGISTRADO' => 'Muestra registrada', 'EN_INSPECCION' => 'En inspección', 'PENDIENTE_DICTAMEN' => 'Pendiente de dictamen', 'COMPLETADO' => 'Completado'];
        $productos = \App\Models\Lote::forCurrentCompany()->distinct()->orderBy('producto')->pluck('producto');
        return view('livewire.muestreos-index', compact('muestreos', 'estados', 'productos'))
            ->layout('components.layouts.app', ['title' => 'Muestreos']);
    }
}
