<?php

namespace App\Livewire;

use App\Livewire\Concerns\CleanAdminFilters;
use App\Http\Controllers\MaestroController;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MaestrosList extends Component
{
    use CleanAdminFilters;
    #[Locked] public string $catalogType;

    protected function filterKey(): string { return 'maestros.'.auth()->id().'.'.$this->catalogType.'.filters'; }

    protected function authorizeFilters(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (auth()->user()?->isCompanyAdmin() && auth()->user()->id_empresa), 403);
    }

    public function mount(string $catalogType): void { $this->authorizeFilters(); $this->catalogType = $catalogType; $this->restoreFilters(); }

    public function render()
    {
        $this->authorizeFilters();
        return view($this->catalogType === 'ubigeo' ? 'livewire.maestros-ubigeo' : 'livewire.maestros-show', app(MaestroController::class)->listingData($this->filterRequest(), $this->catalogType));
    }
}
