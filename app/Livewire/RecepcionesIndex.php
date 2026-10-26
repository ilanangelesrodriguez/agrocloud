<?php

namespace App\Livewire;

use App\Models\Lote;
use App\Models\Recepcion;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RecepcionesIndex extends Component
{
    use WithPagination;

    public string $tab = 'pendientes';
    public string $buscar = '';
    public string $producto = '';
    public string $desde = '';
    public string $hasta = '';
    public int $porPagina = 15;

    public function updated($property): void
    {
        $this->validate([
            'tab' => 'in:pendientes,confirmadas', 'buscar' => 'nullable|string|max:150',
            'producto' => 'nullable|string|max:150', 'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => array_filter(['nullable', 'date_format:Y-m-d', $this->desde !== '' ? 'after_or_equal:desde' : null]), 'porPagina' => 'in:15,30,50',
        ]);
        if ($property !== 'page') $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->reset('buscar', 'producto', 'desde', 'hasta');
        $this->resetValidation();
        $this->resetPage();
    }

    #[On('recepciones-actualizados')]
    public function actualizar(): void {}

    public function render()
    {
        $buscar = trim(mb_substr($this->buscar, 0, 150));
        $pendientes = Lote::forCurrentCompany()->with('productor')->whereIn('estado', ['PENDIENTE_RECEPCION', 'RECIBIDO'])->whereDoesntHave('recepcion')->whereDoesntHave('muestreos');
        $confirmadas = Recepcion::forCurrentCompany()->with(['lote.productor', 'responsable']);
        if ($buscar !== '') {
            $pendientes->where(fn ($q) => $q->where('codigo_lote', 'ilike', '%'.$buscar.'%')->orWhereHas('productor', fn ($p) => $p->where('nombre_razon_social', 'ilike', '%'.$buscar.'%')->orWhere('ruc', 'ilike', '%'.$buscar.'%')));
            $confirmadas->where(fn ($q) => $q->where('documento', 'ilike', '%'.$buscar.'%')->orWhereHas('lote', fn ($l) => $l->where('codigo_lote', 'ilike', '%'.$buscar.'%')->orWhereHas('productor', fn ($p) => $p->where('nombre_razon_social', 'ilike', '%'.$buscar.'%')->orWhere('ruc', 'ilike', '%'.$buscar.'%'))));
        }
        if ($this->producto !== '') {
            $pendientes->where('producto', $this->producto);
            $confirmadas->whereHas('lote', fn ($q) => $q->where('producto', $this->producto));
        }
        foreach (['desde' => '>=', 'hasta' => '<='] as $field => $operator) {
            if ($this->$field !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->$field)) {
                $pendientes->whereDate(\Illuminate\Support\Facades\DB::raw("(fecha_llegada_prevista AT TIME ZONE 'UTC') AT TIME ZONE 'America/Lima'"), $operator, $this->$field);
                $confirmadas->whereDate(\Illuminate\Support\Facades\DB::raw("(recibido_en AT TIME ZONE 'UTC') AT TIME ZONE 'America/Lima'"), $operator, $this->$field);
            }
        }
        $totales = ['pendientes' => (clone $pendientes)->count(), 'confirmadas' => (clone $confirmadas)->count()];
        $query = $this->tab === 'confirmadas' ? $confirmadas->latest('recibido_en') : $pendientes->latest('id_lote');
        $perPage = in_array($this->porPagina, [15, 30, 50], true) ? $this->porPagina : 15;
        $registros = (clone $query)->paginate($perPage);
        if ($registros->currentPage() > $registros->lastPage()) {
            $this->setPage($registros->lastPage());
            $registros = $query->paginate($perPage);
        }
        $productos = app(\App\Services\MaestroCatalog::class)->options('producto', auth()->user()->id_empresa)->pluck('nombre');
        return view('operaciones.recepciones', compact('registros', 'totales', 'productos'))->layout('components.layouts.app', ['title' => 'Recepción de lotes']);
    }
}
