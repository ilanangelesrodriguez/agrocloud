@php
    $editing = isset($usuario);
    $isSelf = $editing && $usuario->is(auth()->user());
    $cargoActual = old('cargo', $editing ? $usuario->cargo : '');
@endphp

<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="min-w-0 space-y-5">
        <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="user-data-title">
            <div class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 px-5 py-3.5">
                <div class="flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 dark:bg-brand-900/35 text-brand-700 dark:text-brand-300"><x-ui.icon name="user" class="h-5 w-5" /></div>
                    <div>
                        <h3 id="user-data-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Datos del usuario</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Información personal y contacto de la cuenta.</p>
                    </div>
                </div>
            </div>
            <div class="grid gap-x-5 gap-y-4 p-5 sm:grid-cols-2">
                <x-ui.input name="nombre" label="Nombre *" :value="$editing ? $usuario->nombre : null" placeholder="Ej. Juan" autocomplete="given-name" required />
                <x-ui.input name="apellido" label="Apellido *" :value="$editing ? $usuario->apellido : null" placeholder="Ej. Pérez García" autocomplete="family-name" required />
                <x-ui.input name="correo" label="Correo *" type="email" :value="$editing ? $usuario->correo : null" placeholder="Ej. juan.perez@empresa.pe" autocomplete="email" required />
                <x-ui.input name="telefono" label="Teléfono" type="tel" :value="$editing ? $usuario->telefono : null" placeholder="Ej. +51 999 888 777" autocomplete="tel" inputmode="tel" maxlength="30" pattern="^\+?[0-9][0-9\s().-]*$" title="Ingresa entre 7 y 15 dígitos; puedes usar +, espacios, guiones o paréntesis." />
                <x-ui.master-select name="cargo" kind="cargo" label="Cargo" :value="$cargoActual" :company-id="$editing ? $usuario->id_empresa : ($empresa->id_empresa ?? auth()->user()->id_empresa)" />
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="user-access-title">
            <div class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 px-5 py-3.5">
                <div class="flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300"><x-ui.icon name="shield" class="h-5 w-5" /></div>
                    <div>
                        <h3 id="user-access-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Acceso y seguridad</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Define el rol y las credenciales de acceso.</p>
                    </div>
                </div>
            </div>
            <div class="grid gap-x-5 gap-y-4 p-5 sm:grid-cols-2">
                <div class="space-y-2 sm:col-span-2">
                    <label for="id_rol" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Rol *</label>
                    @if ($isSelf)
                        <input type="hidden" name="id_rol" value="{{ $usuario->id_rol }}">
                        <div class="flex min-h-11 items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $usuario->rol?->nombre }}</div>
                        <p class="text-xs text-slate-400">Tu propio rol no se cambia desde Gestión de usuarios.</p>
                    @else
                        <select id="id_rol" name="id_rol" required class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50">
                            <option value="">Selecciona un rol</option>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id_rol }}" @selected((string) old('id_rol', $editing ? $usuario->id_rol : '') === (string) $rol->id_rol)>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_rol')<p class="text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                    @endif
                </div>
                <x-ui.input name="password" label="{{ $editing ? 'Nueva contraseña (opcional)' : 'Contraseña *' }}" type="password" placeholder="{{ $editing ? 'Deja en blanco para conservarla' : 'Ingresa una contraseña segura' }}" :required="! $editing" autocomplete="new-password" />
                <x-ui.input name="password_confirmation" label="{{ $editing ? 'Confirmar nueva contraseña' : 'Confirmar contraseña *' }}" type="password" placeholder="Repite la contraseña" :required="! $editing" autocomplete="new-password" />
            </div>
        </section>
    </div>

    <aside class="min-w-0 space-y-5 xl:sticky xl:top-24" aria-label="Foto y estado del usuario">
        <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="user-photo-title">
            <div class="border-b border-slate-100 dark:border-slate-700 px-5 py-3.5">
                <h3 id="user-photo-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Foto de perfil</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Identifica visualmente al usuario dentro de AgroCloud.</p>
            </div>
            <div class="space-y-3 p-5">
                <div class="flex h-36 items-center justify-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-3">
                    <img data-profile-image @if ($editing && $usuario->foto_perfil_url) src="{{ $usuario->foto_perfil_url }}" @endif alt="Vista previa de la foto de perfil" class="h-28 w-28 rounded-full border-4 border-white object-cover shadow-sm ring-1 ring-slate-200 dark:ring-slate-700 {{ $editing && $usuario->foto_perfil_url ? '' : 'hidden' }}">
                    <div data-profile-empty class="flex flex-col items-center gap-2 text-slate-400 {{ $editing && $usuario->foto_perfil_url ? 'hidden' : '' }}">
                        <x-ui.icon name="user" class="h-10 w-10" />
                        <span class="text-xs font-medium">Aún no hay foto</span>
                    </div>
                </div>
                <div class="rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50/70 dark:bg-slate-800/70 px-3 py-3.5">
                    <label for="foto_perfil" class="block cursor-pointer rounded-xl border border-brand-200 dark:border-brand-800 bg-white dark:bg-slate-900 px-4 py-2.5 text-center text-sm font-semibold text-brand-700 dark:text-brand-300 shadow-sm hover:bg-brand-50 dark:hover:bg-brand-900/35 focus-within:ring-4 focus-within:ring-brand-100 dark:focus-within:ring-brand-900/50">Seleccionar imagen</label>
                    <input id="foto_perfil" name="foto_perfil" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" data-profile-input>
                    <p class="mt-1.5 text-center text-xs text-slate-500 dark:text-slate-400">PNG, JPG o WEBP · máximo 2 MB.</p>
                </div>
                @error('foto_perfil')<p class="text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm" aria-labelledby="user-status-title">
            <h3 id="user-status-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Estado de la cuenta</h3>
            @if ($isSelf)
                <input type="hidden" name="estado" value="1">
                <p class="mt-3 text-xs leading-5 text-slate-500 dark:text-slate-400">Tu propia cuenta se mantiene activa desde esta pantalla. Para tus datos personales usa también <a href="{{ route('perfil.edit') }}" wire:navigate class="font-semibold text-brand-700 dark:text-brand-300">Mi perfil</a>.</p>
            @else
                <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50 dark:bg-slate-800 p-3.5">
                    <input type="hidden" name="estado" value="0">
                    <input type="checkbox" name="estado" value="1" @checked(old('estado', $editing ? $usuario->estado : true)) class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 dark:border-slate-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500">
                    <span>
                        <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Usuario activo</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">Podrá acceder a la plataforma mientras permanezca activo.</span>
                    </span>
                </label>
            @endif
        </section>
    </aside>
</div>

<script>
    (() => {
        const input = document.querySelector('[data-profile-input]');
        const image = document.querySelector('[data-profile-image]');
        const empty = document.querySelector('[data-profile-empty]');
        if (!input || !image || !empty) return;

        let objectUrl;
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file || !file.type.startsWith('image/')) return;
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            image.src = objectUrl;
            image.classList.remove('hidden');
            empty.classList.add('hidden');
        });
    })();
</script>
