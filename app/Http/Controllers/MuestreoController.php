<?php

namespace App\Http\Controllers;

use App\Enums\LoteEstado;
use App\Models\Lote;
use App\Models\Muestreo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MuestreoController extends Controller
{
    public function index(Request $request): View
    {
        $muestreos = Muestreo::query()->forCurrentCompany()
            ->with(['lote.productor', 'inspector'])->withCount('inspecciones')
            ->when($request->filled('buscar'), fn ($query) => $query->whereHas('lote',
                fn ($lote) => $lote->where('codigo_lote', 'ilike', '%'.trim((string) $request->input('buscar')).'%')))
            ->latest('id_muestreo')->paginate(12)->withQueryString();

        return view('muestreos.index', compact('muestreos'));
    }

    public function create(Request $request): View
    {
        $lotes = Lote::query()->forCurrentCompany()->with('productor')
            ->whereNotIn('estado', ['CLASIFICADO', 'RECHAZADO'])
            ->orderByDesc('id_lote')->get();
        $loteSeleccionado = $request->integer('lote');

        return view('muestreos.create', compact('lotes', 'loteSeleccionado'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = (int) $request->user()->id_empresa;
        $data = $request->validate([
            'id_lote' => ['required', 'integer', Rule::exists('lote', 'id_lote')->where('id_empresa', $empresaId)],
            'tamano_muestra' => ['required', 'integer', 'min:1', 'max:100000'],
            'metodo' => ['required', Rule::in(['ALEATORIO', 'SISTEMATICO', 'DIRIGIDO', 'OTRO'])],
            'observaciones' => ['nullable', 'string', 'max:3000'],
        ]);

        $muestreo = DB::transaction(function () use ($data, $empresaId, $request) {
            $lote = Lote::query()->forCurrentCompany()->lockForUpdate()->findOrFail($data['id_lote']);
            abort_if(in_array($lote->estado->value, ['CLASIFICADO', 'RECHAZADO'], true), 422, 'Este lote no admite muestreos nuevos.');
            $muestreo = Muestreo::create([
                ...$data,
                'id_empresa' => $empresaId,
                'id_inspector' => $request->user()->id_usuario,
                'estado' => 'REGISTRADO',
            ]);
            if ($lote->estado === LoteEstado::RECIBIDO) {
                $lote->update(['estado' => LoteEstado::EN_MUESTREO]);
            }
            return $muestreo;
        });

        return redirect()->route('muestreos.show', $muestreo->id_muestreo)
            ->with('success', 'Muestreo registrado. Ahora puedes iniciar la inspección.');
    }

    public function show(int $muestreo): View
    {
        $muestreo = Muestreo::query()->forCurrentCompany()
            ->with(['lote.productor', 'inspector', 'inspecciones.inspector', 'inspecciones.dictamen'])
            ->findOrFail($muestreo);

        return view('muestreos.show', compact('muestreo'));
    }
}
