<?php

namespace App\Livewire;

use App\Models\Inspeccion;
use App\Models\DictamenCalidad;
use App\Models\User;
use App\Services\RevisionEvidencias;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CalidadPendientes extends Component
{
    use WithPagination;

    public string $tab = 'pendientes';
    public string $buscar = '';
    public string $desde = '';
    public string $hasta = '';
    public string $decision = '';
    public string $supervisor = '';

    public function updated(): void
    {
        $this->validate([
            'tab' => 'in:pendientes,historial', 'buscar' => 'nullable|string|max:150',
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => array_filter(['nullable', 'date_format:Y-m-d', $this->desde !== '' ? 'after_or_equal:desde' : null]),
            'decision' => 'nullable|in:APROBADO,OBSERVADO,RECHAZADO', 'supervisor' => 'nullable|integer|min:1',
        ]);
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->reset('buscar', 'desde', 'hasta', 'decision', 'supervisor');
        $this->resetValidation();
        $this->resetPage();
    }

    public function render()
    {
        $empresa = auth()->user()->id_empresa;
        $base = Inspeccion::forCurrentCompany()->where('estado', 'VALIDADA');
        $totales = [
            'pendientes' => (clone $base)->pendingDecision()->count(),
            'historial' => (clone $base)->whereHas('dictamen')->count(),
            'hoy' => DictamenCalidad::where('id_empresa', $empresa)->whereDate('created_at', today())->count(),
        ];
        $query = (clone $base)->with(['muestreo.lote.productor', 'inspector', 'dictamen.supervisor', 'evidencias.validaciones', 'hallazgos']);
        $query = $this->tab === 'historial' ? $query->whereHas('dictamen') : $query->pendingDecision();
        $search = trim(mb_substr($this->buscar, 0, 150));
        if ($search !== '') $query->where(function ($q) use ($search) {
            if (ctype_digit(ltrim($search, '#'))) $q->where('id_inspeccion', (int) ltrim($search, '#'))->orWhereHas('muestreo.lote', fn ($l) => $l->where('codigo_lote', 'ilike', '%'.$search.'%')->orWhereHas('productor', fn ($p) => $p->where('nombre_razon_social', 'ilike', '%'.$search.'%')->orWhere('ruc', 'ilike', '%'.$search.'%')));
            else $q->whereHas('muestreo.lote', fn ($l) => $l->where('codigo_lote', 'ilike', '%'.$search.'%')->orWhereHas('productor', fn ($p) => $p->where('nombre_razon_social', 'ilike', '%'.$search.'%')->orWhere('ruc', 'ilike', '%'.$search.'%')));
        });
        foreach (['desde' => '>=', 'hasta' => '<='] as $field => $operator) {
            if ($this->$field !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->$field)) {
                if ($this->tab === 'historial') $query->whereHas('dictamen', fn ($d) => $d->whereDate('created_at', $operator, $this->$field));
                else $query->whereDate('validada_en', $operator, $this->$field);
            }
        }
        if ($this->tab === 'historial') {
            $query->when($this->decision !== '', fn ($q) => $q->whereHas('dictamen', fn ($d) => $d->where('decision', $this->decision)))
                ->when($this->supervisor !== '', fn ($q) => $q->whereHas('dictamen', fn ($d) => $d->where('id_supervisor', $this->supervisor)))
                ->orderByDesc(DictamenCalidad::select('created_at')->whereColumn('dictamen_calidad.id_inspeccion', 'inspeccion.id_inspeccion')->orderByDesc('id_dictamen')->limit(1));
        } else $query->orderBy('validada_en');
        $inspecciones = $query->orderByDesc('id_inspeccion')->paginate(12);
        $ids = $inspecciones->flatMap(fn ($i) => $i->evidencias->modelKeys());
        $eventos = DB::table('eventos_trazabilidad')->where('id_empresa', $empresa)->where('entidad', 'EvidenciaInspeccion')->where('evento', 'REVISION_IMAGEN')->whereIn('registro', $ids)->orderByDesc('id')->get()->unique('registro');
        $revisadas = [];
        foreach ($inspecciones as $inspection) $revisadas[$inspection->id_inspeccion] = count(app(RevisionEvidencias::class)->revisiones($inspection, $eventos->whereIn('registro', $inspection->evidencias->modelKeys())));
        $supervisores = User::where('id_empresa', $empresa)->where(fn ($q) => $q->whereIn('id_usuario', DictamenCalidad::where('id_empresa', $empresa)->select('id_supervisor'))->orWhere(fn ($active) => $active->where('estado', true)->whereHas('rol', fn ($r) => $r->where('codigo', 'SUPERVISOR')->where('estado', true))))->orderBy('nombre')->get();
        return view('livewire.calidad-pendientes', compact('inspecciones', 'totales', 'revisadas', 'supervisores'));
    }
}
