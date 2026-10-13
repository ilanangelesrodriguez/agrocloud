<?php

namespace App\Livewire\Concerns;

use Illuminate\Http\Request;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Livewire\Attributes\Locked;

trait CleanAdminFilters
{
    use WithPagination, WithoutUrlPagination;

    public string $q = '';
    public string $estado = '';
    public string $tipo = '';
    public string $rol = '';
    #[Locked] public array $filters = [];

    public function filtrar(): void
    {
        $this->authorizeFilters();
        $this->q = trim($this->q);
        $this->validate(['q' => ['nullable', 'string', 'max:150'], 'estado' => ['nullable', 'in:0,1'], 'tipo' => ['nullable', 'string', 'max:80'], 'rol' => ['nullable', 'integer']]);
        $this->filters = array_filter(['q' => $this->q, 'estado' => $this->estado, 'tipo' => $this->tipo, 'rol' => $this->rol], fn ($value) => $value !== '');
        session()->put($this->filterKey(), $this->filters);
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->authorizeFilters();
        $this->reset(['q', 'estado', 'tipo', 'rol', 'filters']);
        $this->resetValidation();
        session()->forget($this->filterKey());
        $this->resetPage();
    }

    protected function restoreFilters(): void
    {
        $this->filters = session()->get($this->filterKey(), []);
        foreach (['q', 'estado', 'tipo', 'rol'] as $field) $this->$field = (string) ($this->filters[$field] ?? '');
    }

    protected function filterRequest(): Request
    {
        return new Request($this->filters);
    }

    protected function authorizeFilters(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
