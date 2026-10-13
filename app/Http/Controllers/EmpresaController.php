<?php

namespace App\Http\Controllers;

use App\Events\CompanySecurityUpdated;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Empresa;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class EmpresaController extends Controller
{
    public function edit(): View
    {
        $empresa = $this->currentCompany();
        $canEdit = auth()->user()?->hasPermission('empresa.editar') ?? false;

        return view('empresa.edit', compact('empresa', 'canEdit'));
    }

    public function update(UpdateCompanyRequest $request, CloudinaryService $cloudinary): RedirectResponse
    {
        $empresa = $this->currentCompany();
        $data = $request->safe()->except(['logo']);
        $data = app(\App\Services\MaestroCatalog::class)->validate($data, ['tipo_empresa' => 'tipo_empresa', 'rubro' => 'rubro', 'grupo_empresarial' => 'grupo'], null, isset($empresa) ? $empresa->toArray() : []);
        $geoFields = ['departamento', 'provincia', 'distrito', 'ubigeo'];
        if (collect($geoFields)->contains(fn ($field) => ($data[$field] ?? null) !== $empresa->$field)) {
            $data = app(\App\Services\UbigeoService::class)->normalize($data);
        }

        // Un ADMIN de empresa puede editar sus datos, pero no activar/desactivar el tenant.
        unset($data['estado']);

        $oldPublicId = $empresa->logo_public_id;
        $newUpload = null;

        try {
            if ($request->hasFile('logo')) {
                $newUpload = $cloudinary->uploadImage($request->file('logo'), 'agrocloud/empresas');
                $data['logo_url'] = $newUpload['url'];
                $data['logo_public_id'] = $newUpload['public_id'];
            }

            $empresa->update($data);
        } catch (Throwable $e) {
            if ($newUpload) {
                rescue(fn () => $cloudinary->deleteImage($newUpload['public_id']), report: true);
            }

            report($e);

            return back()->withInput()->withErrors([
                'logo' => 'No se pudo guardar la empresa o subir el logo. Revisa la configuración de Cloudinary.',
            ]);
        }

        if ($newUpload && $oldPublicId) {
            rescue(fn () => $cloudinary->deleteImage($oldPublicId), report: true);
        }

        CompanySecurityUpdated::dispatch($empresa->id_empresa, 'empresa', 'updated', $empresa->id_empresa);

        return back()->with('success', 'Datos de la empresa actualizados correctamente.');
    }

    private function currentCompany(): Empresa
    {
        $empresaId = auth()->user()?->id_empresa;
        abort_unless($empresaId, 404, 'El usuario no tiene una empresa asignada.');

        return Empresa::query()->findOrFail($empresaId);
    }
}
