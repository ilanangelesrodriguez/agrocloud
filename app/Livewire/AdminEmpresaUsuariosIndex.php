<?php

namespace App\Livewire;

use App\Http\Controllers\Admin\EmpresaUsuarioController;
use App\Models\Empresa;
use App\Livewire\Concerns\CleanAdminFilters;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AdminEmpresaUsuariosIndex extends Component
{
    use CleanAdminFilters;
    #[Locked] public int $empresaId;

    protected function filterKey(): string { return 'admin.empresa.'.$this->empresaId.'.usuarios.filters'; }

    public function mount(int $empresaId): void { $this->authorizeFilters(); $this->empresaId = $empresaId; $this->restoreFilters(); }

    public function render()
    {
        $this->authorizeFilters();
        return view('livewire.admin-empresa-usuarios-index', app(EmpresaUsuarioController::class)->listingData($this->filterRequest(), Empresa::findOrFail($this->empresaId)));
    }
}
