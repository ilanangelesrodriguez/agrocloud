<x-layouts.app title="Permisos del rol">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="space-y-2"><x-ui.back-link :href="route('roles.index')" label="Volver a roles" /><div><h2 class="text-2xl font-semibold text-slate-950 dark:text-slate-100">{{ $rol->nombre }}</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Configuración global reservada para SUPERADMIN.</p></div></div>
        <form method="POST" action="{{ route('roles.update', $rol) }}">@csrf @method('PUT')
            <div class="space-y-5">
                @foreach ($permisos as $modulo => $items)
                    <x-ui.card :title="$modulo"><div class="grid gap-3 md:grid-cols-2">@foreach ($items as $permiso)<label class="flex gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-4"><input type="checkbox" name="permisos[]" value="{{ $permiso->id_permiso }}" @checked($rol->permisos->contains('id_permiso', $permiso->id_permiso)) class="mt-0.5 h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500"><span><span class="block text-sm font-medium text-slate-900 dark:text-slate-100">{{ $permiso->nombre }}</span><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $permiso->codigo }} · {{ $permiso->estado ? 'activo' : 'inactivo' }}</span></span></label>@endforeach</div></x-ui.card>
                @endforeach
                @if (! in_array($rol->codigo, ['ADMIN', 'SUPERADMIN']))<label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200"><input type="hidden" name="estado" value="0"><input type="checkbox" name="estado" value="1" @checked(old('estado', $rol->estado)) class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500">Rol activo</label>@else<input type="hidden" name="estado" value="1">@endif
                <div class="flex justify-end"><x-ui.button type="submit"><x-ui.icon name="save" class="mr-2 h-4 w-4" />Guardar permisos</x-ui.button></div>
            </div>
        </form>
    </div>
</x-layouts.app>
