<?php

namespace App\Livewire;

use App\Models\Inspeccion;
use Livewire\Component;
use Livewire\WithPagination;

class CalidadPendientes extends Component
{
    use WithPagination;

    public function render()
    {
        $inspecciones = Inspeccion::query()->forCurrentCompany()
            ->where('estado', 'VALIDADA')->whereDoesntHave('dictamen')
            ->with('muestreo.lote')->latest('id_inspeccion')->paginate(12);

        return view('livewire.calidad-pendientes', compact('inspecciones'));
    }
}
