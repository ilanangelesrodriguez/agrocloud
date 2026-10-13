<?php

namespace App\Http\Controllers;

use App\Enums\LoteEstado;
use App\Models\Lote;
use App\Models\Productor;
use App\Services\MaestroCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoteController extends Controller
{
    /**
     * Listado de lotes.
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar'));

        $lotes = Lote::query()
            ->forCurrentCompany()
            ->with('productor')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query
                        ->where('codigo_lote', 'ilike', "%{$buscar}%")
                        ->orWhere('producto', 'ilike', "%{$buscar}%")
                        ->orWhere('variedad', 'ilike', "%{$buscar}%")
                        ->orWhereHas('productor', function ($productorQuery) use ($buscar) {
                            $productorQuery
                                ->where('nombre_razon_social', 'ilike', "%{$buscar}%")
                                ->orWhere('ruc', 'ilike', "%{$buscar}%");
                        });
                });
            })
            ->orderByDesc('fecha_recepcion')
            ->orderByDesc('id_lote')
            ->paginate(10)
            ->withQueryString();

        return view('lotes.index', compact('lotes', 'buscar'));
    }

    /**
     * Formulario para registrar un nuevo lote.
     */
    public function create(): View
    {
        $productores = Productor::query()
            ->forCurrentCompany()
            ->orderBy('nombre_razon_social')
            ->get();

        $estados = [LoteEstado::RECIBIDO];

        return view('lotes.create', compact('productores', 'estados'));
    }

    /**
     * Registrar un nuevo lote.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_productor' => [
                'required',
                'integer',
            ],

            'codigo_lote' => [
                'required',
                'string',
                'max:50',
                Rule::unique('lote', 'codigo_lote')
                    ->where(function ($query) {
                        return $query->where(
                            'id_empresa',
                            auth()->user()->id_empresa
                        );
                    }),
            ],

            'producto' => [
                'required',
                'string',
                'max:150',
            ],

            'variedad' => [
                'nullable',
                'string',
                'max:150',
            ],

            'unidad_medida' => ['nullable', 'string', 'max:40'],
            'peso_kg' => [
                'required',
                'numeric',
                'min:0',
            ],

            'fecha_recepcion' => [
                'required',
                'date',
            ],

            'estado' => [
                'required',
                Rule::in(['RECIBIDO']),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verificar que el productor pertenece a la empresa actual
        |--------------------------------------------------------------------------
        */

        $productor = Productor::query()
            ->forCurrentCompany()
            ->findOrFail($data['id_productor']);

        /*
        |--------------------------------------------------------------------------
        | Crear lote
        |--------------------------------------------------------------------------
        */

        $data['variedad'] = $data['variedad'] ?? null;
        $data['unidad_medida'] = $data['unidad_medida'] ?? 'Kilogramo';
        $catalog = app(MaestroCatalog::class);
        $data = $catalog->validate($data, ['producto' => 'producto', 'variedad' => 'variedad', 'unidad_medida' => 'unidad'], auth()->user()->id_empresa);
        $data['peso_kg'] = $catalog->pesoKg((float) $data['peso_kg'], $data['unidad_medida']);
        Lote::create([
            'id_productor' => $productor->id_productor,
            'codigo_lote' => $data['codigo_lote'],
            'producto' => $data['producto'],
            'variedad' => $data['variedad'] ?? null,
            'peso_kg' => $data['peso_kg'],
            'unidad_medida' => $data['unidad_medida'],
            'fecha_recepcion' => $data['fecha_recepcion'],
            'estado' => $data['estado'],
        ]);

        return redirect()
            ->route(auth()->user()->hasPermission('recepciones.crear') ? 'recepciones.index' : 'lotes.index')
            ->with(
                'success',
                'El lote fue registrado correctamente.'
            );
    }

    /**
     * Mostrar detalle de un lote.
     */
    public function show(int $lote): View
    {
        $loteModel = Lote::query()
            ->forCurrentCompany()
            ->with('productor')
            ->findOrFail($lote);

        return view('lotes.show', [
            'lote' => $loteModel,
        ]);
    }

    /**
     * Formulario para editar un lote.
     */
    public function edit(int $lote): View
    {
        $loteModel = Lote::query()
            ->forCurrentCompany()
            ->findOrFail($lote);

        abort_if($loteModel->recepcion()->exists() || $loteModel->muestreos()->exists(), 422, 'El lote ya ingresó al flujo operativo. Sus datos se conservan para trazabilidad.');

        $productores = Productor::query()
            ->forCurrentCompany()
            ->orderBy('nombre_razon_social')
            ->get();

        $estados = [$loteModel->estado];

        return view('lotes.edit', [
            'lote' => $loteModel,
            'productores' => $productores,
            'estados' => $estados,
        ]);
    }

    /**
     * Actualizar un lote.
     */
    public function update(
        Request $request,
        int $lote
    ): RedirectResponse {
        return DB::transaction(function () use ($request, $lote) {
            $loteModel = Lote::query()
                ->forCurrentCompany()
                ->lockForUpdate()
                ->findOrFail($lote);

            abort_if($loteModel->recepcion()->exists() || $loteModel->muestreos()->exists(), 422, 'El lote ya ingresó al flujo operativo. Sus datos se conservan para trazabilidad.');

            $data = $request->validate([
                'id_productor' => [
                    'required',
                    'integer',
                ],

                'codigo_lote' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('lote', 'codigo_lote')
                        ->where(function ($query) {
                            return $query->where(
                                'id_empresa',
                                auth()->user()->id_empresa
                            );
                        })
                        ->ignore($loteModel->id_lote, 'id_lote'),
                ],

                'producto' => [
                'required',
                'string',
                'max:150',
            ],

                'variedad' => [
                'nullable',
                'string',
                'max:150',
            ],

                'unidad_medida' => ['nullable', 'string', 'max:40'],
                'peso_kg' => [
                'required',
                'numeric',
                'min:0',
            ],

                'fecha_recepcion' => [
                'required',
                'date',
            ],

                'estado' => [
                'required',
                Rule::in([$loteModel->estado->value]),
            ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Verificar que el productor pertenece a la empresa actual
            |--------------------------------------------------------------------------
            */

            $productor = Productor::query()
                ->forCurrentCompany()
                ->findOrFail($data['id_productor']);

            /*
            |--------------------------------------------------------------------------
            | Actualizar
            |--------------------------------------------------------------------------
            */

            $data['variedad'] = $data['variedad'] ?? null;
            $data['unidad_medida'] = $data['unidad_medida'] ?? 'Kilogramo';
            $catalog = app(MaestroCatalog::class);
            $data = $catalog->validate($data, ['producto' => 'producto', 'variedad' => 'variedad', 'unidad_medida' => 'unidad'], auth()->user()->id_empresa, $loteModel->toArray());
            $data['peso_kg'] = $catalog->pesoKg((float) $data['peso_kg'], $data['unidad_medida']);
            $loteModel->update([
                'id_productor' => $productor->id_productor,
                'codigo_lote' => $data['codigo_lote'],
                'producto' => $data['producto'],
                'variedad' => $data['variedad'] ?? null,
                'peso_kg' => $data['peso_kg'],
                'unidad_medida' => $data['unidad_medida'],
                'fecha_recepcion' => $data['fecha_recepcion'],
                'estado' => $data['estado'],
            ]);

            return redirect()
                ->route('lotes.show', $loteModel->id_lote)
                ->with(
                    'success',
                    'El lote fue actualizado correctamente.'
                );
        });
    }
}
