<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Models\Permiso;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermisoController extends Controller
{
    public function index(): View
    {
        $permisos = Permiso::query()->withCount('roles')->orderBy('modulo')->orderBy('nombre')->paginate(20);

        return view('permisos.index', compact('permisos'));
    }

    public function create(): View
    {
        return view('permisos.create');
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['codigo'] = strtolower($data['codigo']);
        $data['modulo'] = strtoupper($data['modulo']);
        $data['estado'] = $request->boolean('estado');

        Permiso::query()->create($data);

        return redirect()->route('permisos.index')->with('success', 'Permiso global creado correctamente.');
    }

    public function edit(Permiso $permiso): View
    {
        return view('permisos.edit', compact('permiso'));
    }

    public function update(UpdatePermissionRequest $request, Permiso $permiso): RedirectResponse
    {
        $data = $request->validated();
        $data['codigo'] = strtolower($data['codigo']);
        $data['modulo'] = strtoupper($data['modulo']);
        $data['estado'] = $request->boolean('estado');

        $permiso->update($data);

        return redirect()->route('permisos.index')->with('success', 'Permiso global actualizado correctamente.');
    }

    public function toggleStatus(Permiso $permiso): RedirectResponse
    {
        $permiso->update(['estado' => ! $permiso->estado]);

        return back()->with('success', 'Estado del permiso global actualizado.');
    }
}
