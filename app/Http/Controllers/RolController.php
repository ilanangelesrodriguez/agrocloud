<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\UpdateRolePermissionsRequest;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RolController extends Controller
{
    public function index(): View
    {
        $roles = Rol::query()->withCount(['usuarios', 'permisos'])->orderBy('nombre')->get();

        return view('roles.index', compact('roles'));
    }

    public function edit(Rol $rol): View
    {
        $rol->load('permisos');
        $permisos = Permiso::query()->orderBy('modulo')->orderBy('nombre')->get()->groupBy('modulo');

        return view('roles.edit', compact('rol', 'permisos'));
    }

    public function update(UpdateRolePermissionsRequest $request, Rol $rol): RedirectResponse
    {
        // ADMIN y SUPERADMIN son roles estructurales; su estado no se desactiva aquí.
        if (! in_array($rol->codigo, ['ADMIN', 'SUPERADMIN'], true)) {
            $rol->update(['estado' => $request->boolean('estado')]);
        }

        $permissionIds = collect($request->input('permisos', []))
            ->map(fn ($id) => (int) $id)
            ->all();

        $rol->permisos()->sync($permissionIds);

        // Los roles son globales. Cuando se implemente UI global en tiempo real,
        // debe emitirse por un canal de plataforma, no por empresa.{id}.security.
        return redirect()->route('roles.index')->with('success', 'Permisos globales del rol actualizados.');
    }
}
