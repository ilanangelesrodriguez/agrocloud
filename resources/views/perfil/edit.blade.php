<x-layouts.app title="Mi perfil">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="space-y-2">
            <x-ui.back-link :href="route('dashboard')" label="Volver al dashboard" />
            <div>
                <p class="text-sm font-semibold text-brand-700">Cuenta personal</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">Mi perfil</h2>
                <p class="mt-1 text-sm text-slate-500">Actualiza tus datos personales y contraseña. Empresa, rol, correo y cargo son datos administrativos.</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.35fr_.65fr]">
            <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <x-ui.card title="Datos personales" description="Información visible en tu sesión y trazabilidad futura.">
                    <div class="mb-6 flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        @if ($user->foto_perfil_url)
                            <img src="{{ $user->foto_perfil_url }}" alt="Foto de perfil" class="h-20 w-20 rounded-full border-4 border-white object-cover shadow-sm">
                        @else
                            <div class="grid h-20 w-20 place-items-center rounded-full bg-white text-xl font-semibold text-slate-500 shadow-sm">{{ strtoupper(substr($user->nombre, 0, 1)) }}{{ strtoupper(substr($user->apellido ?? '', 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-900">{{ $user->nombre_completo }}</p>
                            <p class="truncate text-sm text-slate-500">{{ $user->correo }}</p>
                            <label class="mt-2 inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-brand-700">
                                Cambiar foto
                                <input name="foto_perfil" type="file" accept="image/png,image/jpeg,image/webp" class="hidden">
                            </label>
                            @error('foto_perfil')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <x-ui.input name="nombre" label="Nombre" :value="$user->nombre" required />
                        <x-ui.input name="apellido" label="Apellido" :value="$user->apellido" required />
                        <x-ui.input name="telefono" label="Teléfono" :value="$user->telefono" />

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-700">Correo</label>
                            <div class="flex min-h-11 items-center rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm text-slate-600">{{ $user->correo }}</div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-700">Cargo</label>
                            <div class="flex min-h-11 items-center rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm text-slate-600">{{ $user->cargo ?: 'Sin cargo asignado' }}</div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-700">Rol</label>
                            <div class="flex min-h-11 items-center rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm text-slate-600">{{ $user->rol?->nombre ?? 'Sin rol' }}</div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end"><x-ui.button type="submit"><x-ui.icon name="save" class="mr-2 h-4 w-4" />Guardar perfil</x-ui.button></div>
                </x-ui.card>
            </form>

            <div class="space-y-6">
                <x-ui.card title="Contexto de trabajo">
                    <div class="space-y-4 text-sm">
                        <div class="flex gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-ui.icon name="building" class="h-4 w-4" /></span><div><p class="text-xs uppercase tracking-wide text-slate-400">Empresa</p><p class="font-medium text-slate-900">{{ $user->empresa?->nombre_comercial ?? 'Administración global' }}</p></div></div>
                        <div class="flex gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600"><x-ui.icon name="briefcase" class="h-4 w-4" /></span><div><p class="text-xs uppercase tracking-wide text-slate-400">Cargo</p><p class="font-medium text-slate-900">{{ $user->cargo ?: 'Sin cargo asignado' }}</p></div></div>
                        <div class="flex gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600"><x-ui.icon name="shield" class="h-4 w-4" /></span><div><p class="text-xs uppercase tracking-wide text-slate-400">Rol</p><p class="font-medium text-slate-900">{{ $user->rol?->nombre ?? 'Sin rol' }}</p></div></div>
                    </div>
                </x-ui.card>

                <form method="POST" action="{{ route('perfil.password.update') }}">
                    @csrf
                    @method('PUT')
                    <x-ui.card title="Contraseña" description="Usa tu contraseña actual para confirmar el cambio.">
                        <div class="space-y-4">
                            <x-ui.input name="current_password" label="Contraseña actual" type="password" autocomplete="current-password" required />
                            <x-ui.input name="password" label="Nueva contraseña" type="password" autocomplete="new-password" required />
                            <x-ui.input name="password_confirmation" label="Confirmar nueva contraseña" type="password" autocomplete="new-password" required />
                            <x-ui.button type="submit" class="w-full"><x-ui.icon name="lock" class="mr-2 h-4 w-4" />Cambiar contraseña</x-ui.button>
                        </div>
                    </x-ui.card>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
