<?php

namespace App\Livewire;

use App\Http\Controllers\Admin\EmpresaAdminController;
use App\Livewire\Concerns\CleanAdminFilters;
use Livewire\Component;

class AdminEmpresasIndex extends Component
{
    use CleanAdminFilters;

    protected function filterKey(): string { return 'admin.empresas.filters'; }

    public function mount(): void { $this->authorizeFilters(); $this->restoreFilters(); }

    public function render()
    {
        $this->authorizeFilters();
        return view('livewire.admin-empresas-index', app(EmpresaAdminController::class)->listingData($this->filterRequest()));
    }
}
