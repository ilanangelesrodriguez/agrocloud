<?php

namespace App\Livewire;

use App\Http\Controllers\MaestroController;
use Illuminate\Http\Request;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class MaestrosList extends Component
{
    use WithPagination, WithoutUrlPagination;

    #[Locked] public string $catalogType;
    #[Locked] public array $filters = [];
    public string $q = '';
    public string $estado = '';
    public string $origen = '';
    public string $parent = '';
    public string $departamento = '';
    public string $provincia = '';
    public string $distrito = '';

    protected function authorizeFilters(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (auth()->user()?->isCompanyAdmin() && auth()->user()->id_empresa), 403);
    }

    protected function filterKey(): string { return 'maestros.'.auth()->id().'.'.$this->catalogType.'.filters'; }

    public function mount(string $catalogType): void
    {
        $this->authorizeFilters();
        abort_unless(isset(config('maestros')[$catalogType]), 404);
        $this->catalogType = $catalogType;
        $saved = session()->get($this->filterKey(), []);
        foreach (['q', 'estado', 'origen', 'parent', 'departamento', 'provincia', 'distrito'] as $key) $this->$key = (string) ($saved[$key] ?? '');
        $this->filtrar();
    }

    public function updated(string $key): void
    {
        if ($key === 'departamento') { $this->provincia = ''; $this->distrito = ''; }
        if ($key === 'provincia') $this->distrito = '';
        if (in_array($key, ['q', 'estado', 'origen', 'parent', 'departamento', 'provincia', 'distrito'], true)) $this->filtrar();
    }

    public function filtrar(): void
    {
        $this->authorizeFilters();
        $this->q = trim($this->q);
        $values = $this->validate(['q' => 'nullable|string|max:150', 'estado' => 'nullable|in:0,1', 'origen' => 'nullable|in:global,company', 'parent' => 'nullable|integer|min:1', 'departamento' => 'nullable|string|max:10', 'provincia' => 'nullable|string|max:10', 'distrito' => 'nullable|string|max:10']);
        $this->filters = array_filter($values, fn ($value) => $value !== '' && $value !== null);
        session()->put($this->filterKey(), $this->filters);
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->reset(['q', 'estado', 'origen', 'parent', 'departamento', 'provincia', 'distrito', 'filters']);
        session()->forget($this->filterKey());
        $this->resetValidation();
        $this->resetPage();
    }

    #[On('masters-saved')]
    public function refreshCatalog(): void {}

    public function render()
    {
        $this->authorizeFilters();
        return view($this->catalogType === 'ubigeo' ? 'livewire.maestros-ubigeo' : 'livewire.maestros-show', app(MaestroController::class)->listingData(new Request($this->filters), $this->catalogType));
    }
}
