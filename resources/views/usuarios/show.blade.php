<x-layouts.app title="Detalle del usuario">
    <section data-user-detail class="space-y-5">
        <x-ui.back-link :href="route('usuarios.index')" label="Volver a usuarios" />
        <div><h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $usuario->nombre_completo }}</h1><p class="mt-1 break-all text-sm text-slate-500 dark:text-slate-400">{{ $usuario->correo }}</p></div>
        <x-ui.state-badge :state="$usuario->estado ? 'ACTIVO' : 'INACTIVO'" />
        <dl class="grid gap-4 rounded-xl bg-white p-5 sm:grid-cols-2 dark:bg-slate-900">@foreach(['Rol' => $usuario->rol?->nombre, 'Cargo' => $usuario->cargo, 'Teléfono' => $usuario->telefono, 'Último acceso' => $usuario->ultimo_acceso?->copy()->timezone('America/Lima')->format('d/m/Y H:i')] as $label => $value)<div><dt class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900 dark:text-slate-100">{{ $value ?: 'No registrado' }}</dd></div>@endforeach</dl>
        <x-ui.card title="Módulos habilitados por su rol"><div class="flex flex-wrap gap-2">@forelse($usuario->rol?->permisos?->where('estado', true)->pluck('modulo')->filter()->unique() ?? [] as $module)<span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ ucfirst(mb_strtolower(str_replace('_', ' ', $module))) }}</span>@empty<p class="text-sm text-slate-500">Sin módulos habilitados.</p>@endforelse</div><p class="mt-4 text-xs text-slate-500">La cuenta, el rol y la empresa deben estar activos para permitir el acceso.</p></x-ui.card>
    </section>
</x-layouts.app>
