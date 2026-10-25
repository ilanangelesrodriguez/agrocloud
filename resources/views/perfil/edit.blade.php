<x-layouts.app title="Mi perfil">
<div class="mx-auto max-w-7xl space-y-6">
    <header><x-ui.back-link :href="route('dashboard')" label="Volver al dashboard" /><p class="mt-4 text-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">Cuenta personal</p><h1 class="mt-2 text-2xl font-semibold dark:text-slate-100 sm:text-3xl">Mi perfil</h1><p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Administra tus datos personales, seguridad y avisos. Cada sección se guarda por separado.</p></header>
    <dl class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-3 dark:border-slate-700 dark:bg-slate-900">@foreach(['Empresa' => $user->empresa?->nombre_comercial ?? 'Administración global', 'Rol' => $user->rol?->nombre ?? 'Sin rol', 'Cargo' => $user->cargo ?: 'Sin cargo asignado'] as $label => $value)<div class="min-w-0"><dt class="text-xs text-slate-500">{{ $label }}</dt><dd class="mt-1 break-words text-sm font-semibold dark:text-slate-200">{{ $value }}</dd></div>@endforeach</dl>
    <div class="grid items-start gap-6 lg:grid-cols-2">
        <div class="min-w-0 space-y-6">
            <x-ui.card title="Datos personales" description="Actualiza tu nombre, teléfono y foto.">
                <form data-ajax-form data-profile-form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" class="space-y-5">@csrf @method('PUT')
                    <div class="flex flex-col gap-4 rounded-xl bg-slate-50 p-4 sm:flex-row sm:items-center dark:bg-slate-800"><div class="flex h-20 w-20 shrink-0 items-center justify-center"><img data-profile-image data-original-photo="{{ $user->foto_perfil_url }}" @if($user->foto_perfil_url) src="{{ $user->foto_perfil_url }}" @endif alt="Vista previa de tu foto" class="h-20 w-20 rounded-full object-cover {{ $user->foto_perfil_url ? '' : 'hidden' }}"><span data-profile-empty class="grid h-20 w-20 place-items-center rounded-full bg-white text-xl font-semibold text-slate-500 dark:bg-slate-900 {{ $user->foto_perfil_url ? 'hidden' : '' }}">{{ mb_strtoupper(mb_substr($user->nombre, 0, 1).mb_substr($user->apellido ?? '', 0, 1)) }}</span></div><div class="min-w-0"><p class="break-words text-sm font-semibold dark:text-slate-100">{{ $user->nombre_completo }}</p><label for="foto_perfil" class="mt-2 inline-flex min-h-10 cursor-pointer items-center rounded-lg border border-brand-200 px-3 text-sm font-semibold text-brand-700 dark:border-brand-800 dark:text-brand-300">Elegir foto</label><input id="foto_perfil" name="foto_perfil" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" data-profile-input><p data-profile-file-meta class="mt-2 text-xs text-slate-500" aria-live="polite">JPG, PNG o WebP · máximo 2 MB.</p>@error('foto_perfil')<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div></div>
                    <div class="grid gap-4 xl:grid-cols-2"><x-ui.input name="nombre" label="Nombre *" :value="$user->nombre" autocomplete="given-name" required /><x-ui.input name="apellido" label="Apellido *" :value="$user->apellido" autocomplete="family-name" required /></div>
                    <x-ui.input name="telefono" label="Teléfono" type="tel" :value="$user->telefono" autocomplete="tel" maxlength="30" />
                    <div class="flex justify-end border-t border-slate-100 pt-4 dark:border-slate-700"><x-ui.button type="submit" class="w-full sm:w-auto">Guardar datos personales</x-ui.button></div>
                </form>
            </x-ui.card>
            <x-ui.card title="Notificaciones internas" description="Elige qué avisos habituales quieres silenciar. Los avisos críticos y los correos de seguridad siguen activos.">
                <form data-ajax-form method="POST" action="{{ route('profile.notifications') }}" class="space-y-4">@csrf
                    <div class="space-y-2">@foreach(\App\Services\OperationalNotifier::OPTIONAL_KINDS as $kind => $label)<label class="flex min-h-11 items-center gap-3 rounded-xl bg-slate-50 px-3 py-2 text-sm dark:bg-slate-800 dark:text-slate-200"><input type="checkbox" name="muted[]" value="{{ $kind }}" @checked(in_array($kind, $user->muted_operational_kinds ?? [], true))>Silenciar {{ mb_strtolower($label) }}</label>@endforeach</div>
                    <div class="flex justify-end"><x-ui.button type="submit" variant="secondary" class="w-full sm:w-auto">Guardar preferencias</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>
        <div class="min-w-0 space-y-6">
            <x-ui.card title="Cambiar contraseña" description="Confirma tu contraseña actual. Se cerrarán las otras sesiones y se solicitará un aviso de seguridad por correo.">
                <form data-ajax-form method="POST" action="{{ route('perfil.password.update') }}" class="space-y-4">@csrf @method('PUT')
                    <x-ui.input name="current_password" id="profile-current-password" label="Contraseña actual *" type="password" autocomplete="current-password" required />
                    <x-ui.input name="password" label="Nueva contraseña *" type="password" autocomplete="new-password" minlength="12" maxlength="255" required />
                    <p class="text-xs text-slate-500">Mínimo 12 caracteres, con mayúsculas, minúsculas y números.</p>
                    <x-ui.input name="password_confirmation" label="Confirmar nueva contraseña *" type="password" autocomplete="new-password" minlength="12" maxlength="255" required />
                    <div class="flex justify-end border-t border-slate-100 pt-4 dark:border-slate-700"><x-ui.button type="submit" class="w-full sm:w-auto">Actualizar contraseña</x-ui.button></div>
                </form>
            </x-ui.card>
            <x-ui.card title="Correo de acceso" description="El correo actual sigue funcionando hasta que confirmes la nueva dirección. El enlace de verificación vence en 24 horas.">
                <p class="mb-4 break-all rounded-xl bg-slate-50 p-3 text-sm font-medium dark:bg-slate-800 dark:text-slate-200">{{ $user->correo }}</p>
                @if($user->pending_email)<div class="mb-4 rounded-xl border border-amber-200 p-3 text-sm text-amber-800 dark:border-amber-900 dark:text-amber-200"><p class="break-all">Pendiente de confirmar: {{ $user->pending_email }}</p><p class="mt-2 text-xs">Para reenviar el enlace, vuelve a solicitar esa dirección.</p><form data-ajax-form method="POST" action="{{ route('users.email.cancel', $user) }}" class="mt-3">@csrf<x-ui.button type="submit" variant="secondary">Cancelar cambio pendiente</x-ui.button></form></div>@endif
                <form data-ajax-form method="POST" action="{{ route('profile.email') }}" class="space-y-4">@csrf
                    <x-ui.input name="correo" id="profile-new-email" type="email" label="Nuevo correo *" autocomplete="email" required />
                    <x-ui.input name="current_password" id="profile-email-password" type="password" label="Contraseña actual *" autocomplete="current-password" required />
                    <div class="flex justify-end border-t border-slate-100 pt-4 dark:border-slate-700"><x-ui.button type="submit" class="w-full sm:w-auto">Enviar verificación</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>
    </div>
</div>
</x-layouts.app>
