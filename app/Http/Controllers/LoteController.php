<?php

namespace App\Http\Controllers;

use App\Enums\LoteEstado;
use App\Models\Lote;
use App\Models\Productor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $estados = LoteEstado::cases();

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
                Rule::enum(LoteEstado::class),
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

        Lote::create([
            'id_productor' => $productor->id_productor,
            'codigo_lote' => $data['codigo_lote'],
            'producto' => $data['producto'],
            'variedad' => $data['variedad'] ?? null,
            'peso_kg' => $data['peso_kg'],
            'fecha_recepcion' => $data['fecha_recepcion'],
            'estado' => $data['estado'],
        ]);

        return redirect()
            ->route('lotes.index')
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

        $productores = Productor::query()
            ->forCurrentCompany()
            ->orderBy('nombre_razon_social')
            ->get();

        $estados = LoteEstado::cases();

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
        $loteModel = Lote::query()
            ->forCurrentCompany()
            ->findOrFail($lote);

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
                Rule::enum(LoteEstado::class),
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

        $loteModel->update([
            'id_productor' => $productor->id_productor,
            'codigo_lote' => $data['codigo_lote'],
            'producto' => $data['producto'],
            'variedad' => $data['variedad'] ?? null,
            'peso_kg' => $data['peso_kg'],
            'fecha_recepcion' => $data['fecha_recepcion'],
            'estado' => $data['estado'],
        ]);

        return redirect()
            ->route('lotes.show', $loteModel->id_lote)
            ->with(
                'success',
                'El lote fue actualizado correctamente.'
            );
    }
}
