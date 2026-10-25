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
        $procedures = SamplingProcedure::forCurrentCompany()->latest('id')->paginate(15);
        return view('muestreos.procedures', compact('procedures'));
    }

    public function store(Request $request)
    {
        $this->authorizeCompany($request);
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('sampling_procedures')->where('id_empresa', $request->user()->id_empresa)->where('version', $request->input('version'))],
            'producto' => ['nullable', 'string', 'max:150'],
            'requiere_poblacion' => ['nullable', 'boolean'],
            'version' => ['required', 'string', 'max:30'],
            'metodo' => ['required', 'string', 'max:80'],
            'criterio_muestra' => ['required', 'string', 'max:1500'],
            'seleccion_frutos' => ['required', 'string', 'max:1500'],
            'max_frutos' => ['required', 'integer', 'min:1', 'max:'.config('operations.sampling_max_fruits')],
            'tamano_recomendado' => ['nullable', 'integer', 'min:1', 'lte:max_frutos'],
        ]);
        $data = app(MaestroCatalog::class)->validate($data, ['metodo' => 'metodo', 'producto' => 'producto'], $request->user()->id_empresa);
        SamplingProcedure::create($data + ['id_empresa' => $request->user()->id_empresa, 'activo' => true]);
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
