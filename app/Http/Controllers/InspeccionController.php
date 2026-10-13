<?php

namespace App\Http\Controllers;

use App\Enums\LoteEstado;
use App\Models\Inspeccion;
use App\Models\Muestreo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InspeccionController extends Controller
{
    public function index(): View
    {
        return view('inspecciones.index');
    }

    public function store(Request $request, int $muestreo): RedirectResponse
    {
        abort_unless($request->user()->rol?->codigo === 'INSPECTOR', 403);
        $inspeccion = DB::transaction(function () use ($request, $muestreo) {
            $muestra = Muestreo::query()->forCurrentCompany()->lockForUpdate()->findOrFail($muestreo);
            abort_if($muestra->estado === 'COMPLETADO', 422, 'Este muestreo ya fue completado.');
            // Una única inspección abierta por muestreo; una reinspección crea otra tras el dictamen.
            $abierta = $muestra->inspecciones()->where('estado', 'EN_CAPTURA')->first();
            if ($abierta) {
                return $abierta;
            }
            abort_if($muestra->inspecciones()->where('estado', 'VALIDADA')->exists(), 422,
                'La inspección está validada. Solicita una reinspección mediante el supervisor.');
            $nueva = Inspeccion::create([
                'id_empresa' => $request->user()->id_empresa,
                'id_muestreo' => $muestra->id_muestreo,
                'id_inspector' => $request->user()->id_usuario,
                'estado' => 'EN_CAPTURA',
            ]);
            $muestra->update(['estado' => 'EN_INSPECCION']);
            $muestra->lote->update(['estado' => LoteEstado::EN_INSPECCION]);
            return $nueva;
        });

        return redirect()->route('inspecciones.show', $inspeccion->id_inspeccion);
    }

    public function show(int $inspeccion): View
    {
        $inspeccion = $this->getInspection($inspeccion);
        return view('inspecciones.show', compact('inspeccion'));
    }

    /** Impide que el inspector modifique inspecciones ajenas o cerradas. */
    public static function canOperate(Inspeccion $inspeccion, Request $request): void
    {
        abort_unless($request->user()->rol?->codigo === 'INSPECTOR', 403,
            'Solo un inspector puede realizar esta operación.');
        abort_unless($inspeccion->estado === 'EN_CAPTURA', 422, 'La inspección ya está cerrada.');
        abort_unless($inspeccion->id_inspector === null
            || (int) $inspeccion->id_inspector === (int) $request->user()->id_usuario, 403,
            'Esta inspección está asignada a otro inspector.');
        if ($inspeccion->id_inspector === null) {
            $inspeccion->update(['id_inspector' => $request->user()->id_usuario]);
        }
    }

    private function getInspection(int $id): Inspeccion
    {
        return Inspeccion::query()->forCurrentCompany()->with([
            'muestreo.lote.productor', 'inspector', 'evidencias.validaciones', 'hallazgos', 'dictamen',
        ])->findOrFail($id);
    }
}
