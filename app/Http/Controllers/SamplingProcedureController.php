<?php

namespace App\Http\Controllers;

use App\Models\SamplingProcedure;
use App\Services\MaestroCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SamplingProcedureController extends Controller
{
    private function authorizeCompany(Request $request): void
    {
        abort_unless($request->user()->id_empresa && $request->user()->rol?->codigo === 'ADMIN', 403);
    }

    public function index(Request $request)
    {
        $this->authorizeCompany($request);
        return view('muestreos.procedures');
    }

    public function store(Request $request)
    {
        $this->authorizeCompany($request);
        app(\App\Services\SamplingProcedureManager::class)->create($request->all(), (int) $request->user()->id_empresa);
        return back()->with('success', 'Procedimiento creado. Para cambiar sus instrucciones registra una versión nueva; las muestras anteriores conservan el procedimiento aplicado.');
    }

    public function toggle(Request $request, int $procedure)
    {
        $this->authorizeCompany($request);
        $item = SamplingProcedure::forCurrentCompany()->findOrFail($procedure);
        $item->update(['activo' => !$item->activo]);
        return back()->with('success', 'Disponibilidad del procedimiento actualizada.');
    }
}
