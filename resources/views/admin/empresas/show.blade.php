<x-layouts.app title="Detalle de empresa">
    <div class="space-y-6">
        <x-ui.sunat-status :verification="$empresa->sunat_verificacion" />
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div><x-ui.back-link :href="route('admin.empresas.index')" label="Regresar a empresas" /><p class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">Tenant</p><h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-slate-100">{{ $empresa->nombre_comercial }}</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $empresa->razon_social }} · RUC {{ $empresa->ruc }}</p></div>
            <div class="flex flex-wrap gap-2"><a wire:navigate href="{{ route('admin.empresas.usuarios.index', $empresa) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700"><x-ui.icon name="users" class="h-4 w-4" />Usuarios</a><a wire:navigate href="{{ route('admin.empresas.edit', $empresa) }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700"><x-ui.icon name="pencil" class="h-4 w-4" />Editar empresa</a></div>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.card><p class="text-sm text-slate-500 dark:text-slate-400">Usuarios</p><p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-slate-100">{{ $empresa->usuarios_count }}</p></x-ui.card>
            <x-ui.card><p class="text-sm text-slate-500 dark:text-slate-400">Usuarios activos</p><p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-slate-100">{{ $empresa->usuarios_activos_count }}</p></x-ui.card>
            <x-ui.card><p class="text-sm text-slate-500 dark:text-slate-400">Administradores</p><p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-slate-100">{{ $empresa->administradores_count }}</p></x-ui.card>
            <x-ui.card><p class="text-sm text-slate-500 dark:text-slate-400">Estado</p><div class="mt-3"><x-ui.status-badge :active="$empresa->estado" active-label="Activa" inactive-label="Inactiva" /></div></x-ui.card>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_1fr]">
            <x-ui.card title="Información corporativa" description="Datos centrales del tenant en AgroCloud.">
                <div class="mb-5 flex items-center gap-4">@if($empresa->logo_url)<div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-3"><img src="{{ $empresa->logo_url }}" alt="Logo" class="h-full w-full min-h-0 min-w-0 object-contain"></div>@else<div class="grid h-20 w-20 place-items-center rounded-2xl bg-brand-50 dark:bg-brand-900/35 text-brand-700 dark:text-brand-300"><x-ui.icon name="building" class="h-8 w-8" /></div>@endif<div><p class="font-semibold text-slate-900 dark:text-slate-100">{{ $empresa->nombre_comercial }}</p><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $empresa->grupo_empresarial ?: 'Sin grupo empresarial' }}</p></div></div>
                <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    @foreach ([['RUC',$empresa->ruc],['Tipo',$empresa->tipo_empresa],['Rubro',$empresa->rubro],['Correo',$empresa->correo],['Teléfono',$empresa->telefono],['Sitio web',$empresa->sitio_web],['Departamento',$empresa->departamento],['Provincia / distrito',trim(($empresa->provincia ?: '').' / '.($empresa->distrito ?: ''), ' /')],['Dirección',$empresa->direccion]] as [$label,$value])
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt><dd class="mt-1 text-slate-800 dark:text-slate-100">{{ $value ?: '—' }}</dd></div>
                    @endforeach
                </dl>
                @if($empresa->descripcion)<div class="mt-5 border-t border-slate-100 dark:border-slate-700 pt-5"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Descripción</p><p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $empresa->descripcion }}</p></div>@endif
            </x-ui.card>

            <x-ui.card title="Usuarios recientes" description="Últimas cuentas registradas en esta empresa.">
                <div class="space-y-2">@forelse($ultimosUsuarios as $usuario)<div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 dark:border-slate-700 p-3"><div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $usuario->nombre_completo }}</p><p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $usuario->rol?->nombre ?? 'Sin rol' }} · {{ $usuario->correo }}</p></div><x-ui.status-badge :active="$usuario->estado" /></div>@empty<p class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">Aún no hay usuarios en esta empresa.</p>@endforelse</div>
                <a wire:navigate href="{{ route('admin.empresas.usuarios.index', $empresa) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-brand-700 dark:text-brand-300 hover:text-brand-800 dark:hover:text-brand-200">Ver todos los usuarios <x-ui.icon name="chevron-right" class="h-4 w-4" /></a>
            </x-ui.card>
        </div>

        <x-ui.card title="Control del tenant" description="Desactivar una empresa bloquea el acceso de sus usuarios sin eliminar su información.">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $empresa->estado ? 'La empresa se encuentra activa.' : 'La empresa se encuentra inactiva.' }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">No se eliminan usuarios ni relaciones históricas.</p></div><form data-ajax-form method="POST" action="{{ route('admin.empresas.toggle-status', $empresa) }}">@csrf @method('PATCH')<button type="submit" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition {{ $empresa->estado ? 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/35 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-950/35' : 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/35 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-950/35' }}"><x-ui.icon name="power" class="h-4 w-4" />{{ $empresa->estado ? 'Desactivar empresa' : 'Activar empresa' }}</button></form></div>
        </x-ui.card>
    </div>
</x-layouts.app>
