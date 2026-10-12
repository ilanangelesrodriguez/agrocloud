<?php

namespace App\Livewire;

use App\Models\Inspeccion;
use Livewire\Component;
use Livewire\WithPagination;

class InspeccionesIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $inspecciones = Inspeccion::query()->forCurrentCompany()
            ->with(['muestreo.lote', 'inspector'])->withCount('evidencias')
            ->latest('id_inspeccion')->paginate(12);

        return view('livewire.inspecciones-index', compact('inspecciones'));
    }
}
