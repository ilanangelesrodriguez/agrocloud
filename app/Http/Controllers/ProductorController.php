<?php

namespace App\Http\Controllers;

use App\Models\Productor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductorController extends Controller
{
    /**
     * Mostrar el listado de productores.
     */
    public function index(Request $request): View
    {
        $query = Productor::query()
            ->forCurrentCompany();

        /*
        |--------------------------------------------------------------------------
        | Búsqueda
        |--------------------------------------------------------------------------
        */

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));

            $query->where(function ($q) use ($buscar) {
                $q->where('ruc', 'ilike', "%{$buscar}%")
                    ->orWhere(
                        'nombre_razon_social',
                        'ilike',
                        "%{$buscar}%"
                    )
                    ->orWhere('fundo', 'ilike', "%{$buscar}%")
                    ->orWhere('ubicacion', 'ilike', "%{$buscar}%");
            });
        }

        $productores = $query
            ->orderBy('nombre_razon_social')
            ->paginate(10)
            ->withQueryString();

        return view('productores.index', compact('productores'));
    }

    /**
     * Mostrar formulario para crear un productor.
     */
    public function create(Request $request): View
    {
        return $request->header('X-Workspace-Drawer') ? view('productores._form', ['drawer' => true]) : view('productores.create');
    }

    /**
     * Guardar un nuevo productor.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'ruc' => [
                'required',
                'digits:11',
            ],

            'nombre_razon_social' => [
                'required',
                'string',
                'max:200',
            ],

            'fundo' => [
                'nullable',
                'string',
                'max:150',
            ],

            'departamento' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'distrito' => ['nullable', 'string', 'max:100'],
            'ubigeo' => ['nullable', 'digits:6'],
            'ubicacion' => [
                'nullable',
                'string',
                'max:250',
            ],
        ], [
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.digits' => 'El RUC debe tener exactamente 11 dígitos.',

            'nombre_razon_social.required' =>
                'El nombre o razón social es obligatorio.',

            'nombre_razon_social.max' =>
                'El nombre o razón social no puede superar los 200 caracteres.',

            'fundo.max' =>
                'El fundo no puede superar los 150 caracteres.',

            'ubicacion.max' =>
                'La ubicación no puede superar los 250 caracteres.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verificar RUC dentro de la empresa actual
        |--------------------------------------------------------------------------
        */

        $existe = Productor::query()
            ->forCurrentCompany()
            ->where('ruc', $validated['ruc'])
            ->exists();

        if ($existe) {
            throw \Illuminate\Validation\ValidationException::withMessages(['ruc' => 'Ya existe un productor registrado con este RUC.']);
        }

        /*
        |--------------------------------------------------------------------------
        | Crear productor
        |--------------------------------------------------------------------------
        |
        | BelongsToCompany asignará automáticamente id_empresa
        | para usuarios que no sean SUPERADMIN.
        |
        */

        $validated = app(\App\Services\UbigeoService::class)->normalize($validated);
        $productor = Productor::create($validated);
        if ($request->header('X-Workspace-Drawer')) return response()->json(['saved' => true, 'module' => 'productores', 'message' => 'Productor registrado correctamente.', 'item' => ['id' => $productor->id_productor, 'label' => $productor->nombre_razon_social.' — RUC: '.$productor->ruc]]);

        return redirect()
            ->route('productores.index')
            ->with('success', 'Productor registrado correctamente.');
    }

    /**
     * Mostrar el detalle de un productor.
     */
    public function show(int $productor): View
    {
        $productorModel = Productor::query()
            ->forCurrentCompany()
            ->withCount('lotes')
            ->findOrFail($productor);

        return view(
            'productores.show',
            compact('productorModel')
        );
    }

    /**
     * Mostrar los lotes del productor.
     */
    public function lotes(int $productor): View
    {
        $productorModel = Productor::query()
            ->forCurrentCompany()
            ->findOrFail($productor);

        $lotes = $productorModel
            ->lotes()
            ->with('recepcion')
            ->orderByDesc('fecha_recepcion')
            ->paginate(10)
            ->withQueryString();

        $resumenEstados = $productorModel->lotes()->select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')->get();
        return view(
            'productores.lotes',
            compact('productorModel', 'lotes', 'resumenEstados')
        );
    }

    /**
     * Mostrar formulario para editar un productor.
     */
    public function edit(Request $request, int $productor): View
    {
        $productorModel = Productor::query()
            ->forCurrentCompany()
            ->findOrFail($productor);

        if ($request->header('X-Workspace-Drawer')) return view('productores._form', ['productorModel' => $productorModel, 'drawer' => true]);
        return view(
            'productores.edit',
            compact('productorModel')
        );
    }

    /**
     * Actualizar un productor.
     */
    public function update(
        Request $request,
        int $productor
    ): RedirectResponse|JsonResponse {
        $productorModel = Productor::query()
            ->forCurrentCompany()
            ->findOrFail($productor);

        $validated = $request->validate([
            'ruc' => [
                'required',
                'digits:11',
            ],

            'nombre_razon_social' => [
                'required',
                'string',
                'max:200',
            ],

            'fundo' => [
                'nullable',
                'string',
                'max:150',
            ],

            'departamento' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'distrito' => ['nullable', 'string', 'max:100'],
            'ubigeo' => ['nullable', 'digits:6'],
            'ubicacion' => [
                'nullable',
                'string',
                'max:250',
            ],
        ], [
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.digits' => 'El RUC debe tener exactamente 11 dígitos.',

            'nombre_razon_social.required' =>
                'El nombre o razón social es obligatorio.',

            'nombre_razon_social.max' =>
                'El nombre o razón social no puede superar los 200 caracteres.',

            'fundo.max' =>
                'El fundo no puede superar los 150 caracteres.',

            'ubicacion.max' =>
                'La ubicación no puede superar los 250 caracteres.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verificar RUC duplicado
        |--------------------------------------------------------------------------
        */

        $duplicado = Productor::query()
            ->forCurrentCompany()
            ->where('ruc', $validated['ruc'])
            ->where(
                'id_productor',
                '!=',
                $productorModel->id_productor
            )
            ->exists();

        if ($duplicado) {
            throw \Illuminate\Validation\ValidationException::withMessages(['ruc' => 'Ya existe otro productor registrado con este RUC.']);
        }

        $validated = app(\App\Services\UbigeoService::class)->normalize($validated);
        $productorModel->update($validated);
        if ($request->header('X-Workspace-Drawer')) return response()->json(['saved' => true, 'module' => 'productores', 'message' => 'Productor actualizado correctamente.', 'item' => ['id' => $productorModel->id_productor, 'label' => $productorModel->nombre_razon_social.' — RUC: '.$productorModel->ruc]]);

        return redirect()
            ->route(
                'productores.show',
                $productorModel->id_productor
            )
            ->with('success', 'Productor actualizado correctamente.');
    }

    /**
     * Eliminar un productor.
     */
    public function destroy(Request $request, int $productor): RedirectResponse|JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Buscar únicamente dentro de la empresa actual
        |--------------------------------------------------------------------------
        */

        $productorModel = Productor::query()
            ->forCurrentCompany()
            ->findOrFail($productor);

        /*
        |--------------------------------------------------------------------------
        | No permitir eliminar productores que tengan lotes
        |--------------------------------------------------------------------------
        |
        | Esto evita dejar lotes asociados a un productor que ya no existe.
        |
        */

        if ($productorModel->lotes()->exists()) {
            if ($request->header('X-Workspace-Drawer')) throw \Illuminate\Validation\ValidationException::withMessages(['productor' => 'No se puede eliminar este productor porque tiene lotes asociados.']);
            return redirect()
                ->route('productores.index')
                ->with(
                    'error',
                    'No se puede eliminar este productor porque tiene lotes asociados.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Guardar nombre para el mensaje
        |--------------------------------------------------------------------------
        */

        $nombreProductor = $productorModel->nombre_razon_social;

        /*
        |--------------------------------------------------------------------------
        | Eliminar
        |--------------------------------------------------------------------------
        */

        $productorModel->delete();
        if ($request->header('X-Workspace-Drawer')) return response()->json(['saved' => true, 'module' => 'productores', 'message' => 'Productor eliminado correctamente.']);

        return redirect()
            ->route('productores.index')
            ->with(
                'success',
                "El productor {$nombreProductor} fue eliminado correctamente."
            );
    }
}
