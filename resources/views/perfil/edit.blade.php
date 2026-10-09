<x-layouts.app title="Mi perfil">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="space-y-4">
            <x-ui.back-link :href="route('dashboard')" label="Volver al dashboard" />
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">Cuenta personal</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-100 sm:text-3xl">Mi perfil</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-400">Actualiza tus datos personales y contraseña. Empresa, rol, correo y cargo son datos administrativos.</p>
            </div>
        </div>

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" aria-labelledby="profile-data-title">
                    <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-3.5 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="flex items-start gap-3">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/35 dark:text-brand-300"><x-ui.icon name="user" class="h-5 w-5" /></div>
                            <div>
                                <h3 id="profile-data-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Datos personales</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Información visible en tu sesión y trazabilidad futura.</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-5 p-5">
                        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-800/70 sm:flex-row sm:items-center">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center">
                                <img data-profile-image @if ($user->foto_perfil_url) src="{{ $user->foto_perfil_url }}" @endif alt="Vista previa de foto de {{ $user->nombre_completo }}" class="h-20 w-20 rounded-full border-4 border-white object-cover shadow-sm ring-1 ring-slate-200 dark:border-slate-700 {{ $user->foto_perfil_url ? '' : 'hidden' }}">
                                <span data-profile-empty class="grid h-20 w-20 place-items-center rounded-full bg-white text-xl font-semibold text-slate-500 shadow-sm dark:bg-slate-900 dark:text-slate-400 {{ $user->foto_perfil_url ? 'hidden' : '' }}">{{ strtoupper(substr($user->nombre, 0, 1)) }}{{ strtoupper(substr($user->apellido ?? '', 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900 dark:text-slate-100">{{ $user->nombre_completo }}</p>
                                <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $user->correo }}</p>
                                <label for="foto_perfil" class="mt-2 inline-flex cursor-pointer rounded-xl border border-brand-200 bg-white px-3.5 py-2 text-sm font-semibold text-brand-700 shadow-sm hover:bg-brand-50 focus-within:ring-4 focus-within:ring-brand-100 dark:border-brand-800 dark:bg-slate-900 dark:text-brand-300">Cambiar foto</label>
                                <input id="foto_perfil" name="foto_perfil" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" data-profile-input>
                                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">PNG, JPG o WEBP · máximo 2 MB.</p>
                                @error('foto_perfil')<p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                            <x-ui.input name="nombre" label="Nombre *" :value="$user->nombre" placeholder="Ej. Juan" autocomplete="given-name" required />
                            <x-ui.input name="apellido" label="Apellido *" :value="$user->apellido" placeholder="Ej. Pérez García" autocomplete="family-name" required />
                            <x-ui.input name="telefono" label="Teléfono" type="tel" :value="$user->telefono" placeholder="Ej. +51 999 888 777" autocomplete="tel" inputmode="tel" maxlength="30" pattern="^\+?[0-9][0-9\s().-]*$" title="Ingresa entre 7 y 15 dígitos; puedes usar +, espacios, guiones o paréntesis." />
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Correo</label>
                                <div class="flex min-h-11 items-center rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $user->correo }}</div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cargo</label>
                                <div class="flex min-h-11 items-center rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $user->cargo ?: 'Sin cargo asignado' }}</div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Rol</label>
                                <div class="flex min-h-11 items-center rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $user->rol?->nombre ?? 'Sin rol' }}</div>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-end sm:px-6">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">Cancelar</a>
                    <x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="save" class="h-4 w-4" />Guardar perfil</span></x-ui.button>
                </div>
            </form>

            <aside class="min-w-0 space-y-5 xl:sticky xl:top-24" aria-label="Contexto y seguridad de la cuenta">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" aria-labelledby="profile-context-title">
                    <div class="border-b border-slate-100 px-5 py-3.5 dark:border-slate-700">
                        <h3 id="profile-context-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Contexto de trabajo</h3>
                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Datos administrados por tu empresa.</p>
                    </div>
                    <div class="space-y-4 p-5 text-sm">
                        <div class="flex gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/35 dark:text-brand-300"><x-ui.icon name="building" class="h-4 w-4" /></span><div><p class="text-xs uppercase tracking-wide text-slate-400">Empresa</p><p class="font-medium text-slate-900 dark:text-slate-100">{{ $user->empresa?->nombre_comercial ?? 'Administración global' }}</p></div></div>
                        <div class="flex gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-ui.icon name="briefcase" class="h-4 w-4" /></span><div><p class="text-xs uppercase tracking-wide text-slate-400">Cargo</p><p class="font-medium text-slate-900 dark:text-slate-100">{{ $user->cargo ?: 'Sin cargo asignado' }}</p></div></div>
                        <div class="flex gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-ui.icon name="shield" class="h-4 w-4" /></span><div><p class="text-xs uppercase tracking-wide text-slate-400">Rol</p><p class="font-medium text-slate-900 dark:text-slate-100">{{ $user->rol?->nombre ?? 'Sin rol' }}</p></div></div>
                    </div>
                </section>

                <form method="POST" action="{{ route('perfil.password.update') }}">
                    @csrf
                    @method('PUT')
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" aria-labelledby="profile-password-title">
                        <div class="border-b border-slate-100 px-5 py-3.5 dark:border-slate-700">
                            <h3 id="profile-password-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Contraseña</h3>
                            <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Usa tu contraseña actual para confirmar el cambio.</p>
                        </div>
                        <div class="space-y-4 p-5">
                            <x-ui.input name="current_password" label="Contraseña actual *" type="password" autocomplete="current-password" required />
                            <x-ui.input name="password" label="Nueva contraseña *" type="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password" required />
                            <x-ui.input name="password_confirmation" label="Confirmar nueva contraseña *" type="password" placeholder="Repite la contraseña" autocomplete="new-password" required />
                            <x-ui.button type="submit" class="w-full"><span class="inline-flex items-center gap-2"><x-ui.icon name="lock" class="h-4 w-4" />Cambiar contraseña</span></x-ui.button>
                        </div>
                    </section>
                </form>
            </aside>
        </div>
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
            window.addEventListener('pagehide', () => objectUrl && URL.revokeObjectURL(objectUrl), { once: true });
        })();
    </script>
</x-layouts.app>
