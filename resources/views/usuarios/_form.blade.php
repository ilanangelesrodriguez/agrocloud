@php($editing = isset($usuario))

<div class="grid gap-5 md:grid-cols-2">
    <x-ui.input name="nombre" label="Nombre" :value="$editing ? $usuario->nombre : null" required />
    <x-ui.input name="apellido" label="Apellido" :value="$editing ? $usuario->apellido : null" required />
    <x-ui.input name="correo" label="Correo" type="email" :value="$editing ? $usuario->correo : null" required />
    <x-ui.input name="telefono" label="Teléfono" :value="$editing ? $usuario->telefono : null" />
    <x-ui.input name="cargo" label="Cargo" :value="$editing ? $usuario->cargo : null" />

    <div class="space-y-2">
        <label for="id_rol" class="block text-sm font-medium text-slate-700">Rol</label>
        <select id="id_rol" name="id_rol" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            <option value="">Selecciona un rol</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->id_rol }}" @selected((string) old('id_rol', $editing ? $usuario->id_rol : '') === (string) $rol->id_rol)>
                    {{ $rol->nombre }}
                </option>
            @endforeach
        </select>
        @error('id_rol')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <x-ui.input name="password" label="{{ $editing ? 'Nueva contraseña (opcional)' : 'Contraseña' }}" type="password" :required="! $editing" autocomplete="new-password" />
    <x-ui.input name="password_confirmation" label="Confirmar contraseña" type="password" :required="! $editing" autocomplete="new-password" />
</div>

<div class="mt-5 grid gap-5 md:grid-cols-[1fr_auto] md:items-end">
    <div>
        <label for="foto_perfil" class="block text-sm font-medium text-slate-700">Foto de perfil</label>
        <input id="foto_perfil" name="foto_perfil" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full text-sm text-slate-600">
        <p class="mt-1 text-xs text-slate-400">JPG, PNG o WEBP. Máximo 2 MB. Se almacena en Cloudinary.</p>
        @error('foto_perfil')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    @if ($editing && $usuario->foto_perfil_url)
        <img src="{{ $usuario->foto_perfil_url }}" alt="Foto actual" class="h-20 w-20 rounded-full border border-slate-200 object-cover">
    @endif
</div>

<label class="mt-5 flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="estado" value="0">
    <input type="checkbox" name="estado" value="1" @checked(old('estado', $editing ? $usuario->estado : true)) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
    Usuario activo
</label>
