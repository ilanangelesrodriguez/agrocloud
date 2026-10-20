<?php

namespace App\Livewire;

use App\Models\SolicitudAcceso;
use App\Services\AccesoEmpresarial;
use Livewire\Component;
use Livewire\WithPagination;

class SolicitudesAcceso extends Component
{
    use WithPagination;
    public string $estado = 'PENDIENTE';
    public string $buscar = '';
    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
        $this->estado = session('solicitudes.estado', 'PENDIENTE'); $this->buscar = session('solicitudes.buscar', '');
    }
    public function updated(): void
    {
        $this->validate(['estado' => 'nullable|in:PENDIENTE,EN_REVISION,APROBADA,RECHAZADA', 'buscar' => 'nullable|string|max:150']);
        session(['solicitudes.estado' => $this->estado, 'solicitudes.buscar' => $this->buscar]); $this->resetPage();
    }
    public function limpiar(): void { $this->estado = ''; $this->buscar = ''; $this->updated(); }
    public function render()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
        $habilitado = app(AccesoEmpresarial::class)->habilitado();
        $solicitudes = $habilitado ? SolicitudAcceso::when($this->estado !== '', fn ($q) => $q->where('estado', $this->estado))->when(trim($this->buscar) !== '', fn ($q) => $q->where(fn ($w) => $w->whereLike('razon_social', '%'.trim(mb_substr($this->buscar, 0, 150)).'%')->orWhereLike('ruc', '%'.trim(mb_substr($this->buscar, 0, 150)).'%')))->oldest()->paginate(15) : null;
        $totales = $habilitado ? SolicitudAcceso::select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado') : collect();
        return view('admin.solicitudes.index', compact('solicitudes', 'totales', 'habilitado'));
    }
}
