<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class PerfilController extends Controller
{
    public function edit(): View
    {
        $user = request()->user()->loadMissing(['rol', 'empresa']);

        return view('perfil.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request, CloudinaryService $cloudinary): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['foto_perfil']);
        $oldPublicId = $user->foto_perfil_public_id;
        $uploaded = null;

        try {
            if ($request->hasFile('foto_perfil')) {
                $uploaded = $cloudinary->uploadImage($request->file('foto_perfil'), 'agrocloud/perfiles');
                $data['foto_perfil_url'] = $uploaded['url'];
                $data['foto_perfil_public_id'] = $uploaded['public_id'];
            }

            $user->update($data);
        } catch (Throwable $e) {
            if ($uploaded) {
                rescue(fn () => $cloudinary->deleteImage($uploaded['public_id']), report: true);
            }

            report($e);

            return back()->withInput()->withErrors([
                'foto_perfil' => 'No se pudo actualizar el perfil. Si subiste una foto, revisa la configuración de Cloudinary.',
            ]);
        }

        if ($uploaded && $oldPublicId) {
            rescue(fn () => $cloudinary->deleteImage($oldPublicId), report: true);
        }

        return back()->with('success', 'Tu perfil se actualizó correctamente.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password_hash' => $request->string('password')->toString(),
        ]);

        return back()->with('success', 'Tu contraseña se actualizó correctamente.');
    }
}
