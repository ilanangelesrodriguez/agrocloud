<?php

namespace App\Livewire;

use App\Models\SamplingProcedure;
use App\Services\SamplingProcedureManager;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class SamplingProceduresIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $q = '';
    public string $estado = '';
    public bool $showCreate = false;
    public string $nombre = '';
    public string $version = '';
    public string $producto = '';
    public bool $requiere_poblacion = false;
    public string $metodo = '';
    public string $criterio_muestra = '';
    public string $seleccion_frutos = '';
    public string $max_frutos = '';
    public string $tamano_recomendado = '';

    private function company(): int
    {
        $user = auth()->user();
        abort_unless($user?->isCompanyAdmin() && $user->id_empresa && $user->estado && !$user->invitation_hash && $user->empresa?->estado, 403);
        abort_unless((int) session('account_auth_version', 0) === (int) $user->auth_version, 403);
        return (int) $user->id_empresa;
    }

    public function updatedQ(): void { $this->resetPage(); }
    public function updatedEstado(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->company();
        $this->resetValidation();
        $this->showCreate = true;
    }

    public function closeCreate(): void
    {
        $this->company();
        $this->showCreate = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $company = $this->company();
        $input = $this->only(['nombre', 'version', 'producto', 'requiere_poblacion', 'metodo', 'criterio_muestra', 'seleccion_frutos', 'max_frutos', 'tamano_recomendado']);
        foreach (['producto', 'tamano_recomendado'] as $key) if ($input[$key] === '') $input[$key] = null;
        app(SamplingProcedureManager::class)->create($input, $company);
        $this->reset(['nombre', 'version', 'producto', 'requiere_poblacion', 'metodo', 'criterio_muestra', 'seleccion_frutos', 'max_frutos', 'tamano_recomendado', 'showCreate', 'q', 'estado']);
        $this->resetPage();
        $this->dispatch('agrocloud:toast', message: 'Procedimiento registrado. Ya está disponible para los inspectores.', type: 'success');
    }

    public function toggle(int $id, bool $expected): void
    {
        $company = $this->company();
        DB::transaction(function () use ($id, $expected, $company) {
            $procedure = SamplingProcedure::where('id_empresa', $company)->lockForUpdate()->findOrFail($id);
            abort_unless($procedure->activo === $expected, 409, 'El estado cambió. Actualiza la lista antes de continuar.');
            $procedure->update(['activo' => !$procedure->activo]);
        });
        $this->dispatch('agrocloud:toast', message: 'Disponibilidad actualizada. Las muestras anteriores conservan su versión.', type: 'success');
    }

    public function render()
    {
        $company = $this->company();
        $this->validate(['q' => 'nullable|string|max:150', 'estado' => 'nullable|in:0,1']);
        $procedures = SamplingProcedure::where('id_empresa', $company)
            ->when(trim($this->q) !== '', fn ($q) => $q->where(fn ($sub) => $sub->where('nombre', 'ilike', '%'.trim($this->q).'%')->orWhere('producto', 'ilike', '%'.trim($this->q).'%')->orWhere('version', 'ilike', '%'.trim($this->q).'%')))
            ->when($this->estado !== '', fn ($q) => $q->where('activo', $this->estado === '1'))
            ->latest('id')->paginate(12);
        return view('livewire.sampling-procedures-index', compact('procedures'));
    }
}
