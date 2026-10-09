<?php

namespace App\Http\Controllers;

use App\Enums\LoteEstado;
use App\Models\DictamenCalidad;
use App\Models\Inspeccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CalidadController extends Controller
{
    public function index(): View
    {
        $inspecciones = Inspeccion::query()->forCurrentCompany()
            ->where('estado', 'VALIDADA')->whereDoesntHave('dictamen')
            ->with('muestreo.lote')->latest('id_inspeccion')->paginate(12);
        return view('calidad.index', compact('inspecciones'));
    }

    public function show(int $inspeccion): View
    {
        $inspeccion = Inspeccion::query()->forCurrentCompany()->with([
            'muestreo.lote.productor', 'evidencias.validaciones', 'hallazgos', 'dictamen',
        ])->findOrFail($inspeccion);
        return view('calidad.show', compact('inspeccion'));
    }

    public function store(Request $request, int $inspeccion): RedirectResponse
    {
        abort_unless($request->user()->rol?->codigo === 'SUPERVISOR', 403);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['APROBADO', 'OBSERVADO', 'RECHAZADO'])],
            'categoria' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['required', 'string', 'max:3000'],
        ]);
        DB::transaction(function () use ($request, $inspeccion, $data) {
            $inspection = Inspeccion::query()->forCurrentCompany()->lockForUpdate()->findOrFail($inspeccion);
            abort_unless($inspection->estado === 'VALIDADA', 422, 'La inspección no ha sido validada.');
            abort_if($inspection->dictamen()->exists(), 422, 'Esta inspección ya tiene un dictamen.');
            $muestra = $inspection->muestreo;
            $lote = $muestra->lote;
            DictamenCalidad::create([
                'id_empresa' => $inspection->id_empresa,
                'id_lote' => $lote->id_lote,
                'id_inspeccion' => $inspection->id_inspeccion,
                'id_supervisor' => $request->user()->id_usuario,
                ...$data,
            ]);
            if ($data['decision'] === 'APROBADO') {
                $lote->update(['estado' => LoteEstado::CLASIFICADO]);
            } elseif ($data['decision'] === 'RECHAZADO') {
                $lote->update(['estado' => LoteEstado::RECHAZADO]);
            } else {
                $lote->update(['estado' => LoteEstado::OBSERVADO]);
                // Preserva la inspección validada y abre una nueva inspección para el mismo muestreo.
                Inspeccion::create([
                    'id_empresa' => $inspection->id_empresa,
                    'id_muestreo' => $muestra->id_muestreo,
                    'id_inspector' => null,
                    'solicitada_por' => $request->user()->id_usuario,
                    'estado' => 'EN_CAPTURA',
                ]);
                $muestra->update(['estado' => 'EN_INSPECCION']);
                $lote->update(['estado' => LoteEstado::EN_INSPECCION]);
            }
        });
        return redirect()->route('calidad.index')->with('success', 'Dictamen guardado correctamente.');
    }
}
