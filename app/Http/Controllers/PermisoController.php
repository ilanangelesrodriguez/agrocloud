<?php

namespace App\Http\Controllers;

use App\Events\CompanySecurityUpdated;
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

        $permiso = Permiso::query()->create($data);
        $empresaId = (int) $request->user()->id_empresa;
        CompanySecurityUpdated::dispatch($empresaId, 'permiso', 'created', $permiso->id_permiso);

        return redirect()->route('permisos.index')->with('success', 'Permiso creado correctamente.');
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
        $empresaId = (int) $request->user()->id_empresa;
        CompanySecurityUpdated::dispatch($empresaId, 'permiso', 'updated', $permiso->id_permiso);

        return redirect()->route('permisos.index')->with('success', 'Permiso actualizado correctamente.');
    }

    public function toggleStatus(Permiso $permiso): RedirectResponse
    {
        $permiso->update(['estado' => ! $permiso->estado]);
        $empresaId = (int) auth()->user()->id_empresa;
        CompanySecurityUpdated::dispatch($empresaId, 'permiso', 'status_changed', $permiso->id_permiso);

        return back()->with('success', 'Estado del permiso actualizado.');
    }
}
