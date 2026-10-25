<?php

namespace App\Livewire;

use App\Models\Inspeccion;
use Livewire\Component;
use Livewire\WithPagination;

class InspeccionesIndex extends Component
{
    use WithPagination;

    public string $cola = 'todas';
    public string $buscar = '';
    public bool $misInspecciones = false;

    public function updated(): void
    {
        $this->validate(['cola' => 'in:todas,captura,dictamen,cerradas', 'buscar' => 'nullable|string|max:150']);
        $this->resetPage();
    }

    public function render()
    {
        $inspecciones = Inspeccion::query()->forCurrentCompany()
            ->with(['muestreo.lote', 'inspector', 'dictamen'])->withCount('evidencias')
            ->when($this->cola === 'captura', fn ($q) => $q->where('estado', 'EN_CAPTURA'))
            ->when($this->cola === 'dictamen', fn ($q) => $q->where('estado', 'VALIDADA')->pendingDecision())
            ->when($this->cola === 'cerradas', fn ($q) => $q->whereHas('dictamen'))
            ->when($this->misInspecciones, fn ($q) => $q->where('id_inspector', auth()->id()))
            ->when(trim($this->buscar) !== '', fn ($q) => $q->whereHas('muestreo.lote', fn ($l) => $l->where('codigo_lote', 'ilike', '%'.trim(mb_substr($this->buscar, 0, 150)).'%')))
            ->latest('id_inspeccion')->paginate(12);

        return view('livewire.inspecciones-index', compact('inspecciones'));
    }
}
