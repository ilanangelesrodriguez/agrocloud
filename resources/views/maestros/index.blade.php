<x-layouts.app title="Maestros">
    <div class="space-y-5">
        <div><h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Catálogos maestros</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Mantén las opciones que se utilizan en los formularios. Desactivar una opción conserva los registros anteriores.</p></div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($definitions as $type => $definition)
                <a wire:navigate href="{{ route('maestros.show', $type) }}" class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-brand-400 dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/35 dark:text-brand-300"><x-ui.icon :name="match($type) { 'cargo' => 'briefcase', 'tipo_empresa' => 'building', 'rubro' => 'building-plus', 'producto' => 'leaf', 'variedad' => 'layers', 'categoria' => 'tag', 'metodo' => 'clipboard-list', 'defecto' => 'alert-triangle', 'motivo' => 'shield', 'ubigeo' => 'map-pin', 'grupo' => 'users', default => 'ruler' }" class="h-5 w-5" /></span>
                        <h2 class="font-semibold text-slate-900 dark:text-slate-100">{{ $definition['label'] }}</h2>
                    </div>
                    <p class="flex-1 text-sm text-slate-500 dark:text-slate-400">{{ $definition['description'] }}</p>
                    <div class="flex items-center justify-between gap-3 text-xs font-medium text-brand-700 dark:text-brand-300"><span>{{ match($definition['scope']) { 'global' => 'Administración global', 'company' => 'Administración de empresa', 'shared' => 'Global + extensiones por empresa', default => 'Solo lectura' } }}</span><x-ui.icon name="chevron-right" class="h-4 w-4 shrink-0" /></div>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.app>
