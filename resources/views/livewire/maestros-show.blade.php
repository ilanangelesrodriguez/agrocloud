@php
    $user = auth()->user();
    $canCreate = $definition['scope'] === 'shared' || ($definition['scope'] === 'global' && $user->isSuperAdmin()) || ($definition['scope'] === 'company' && $user->isCompanyAdmin());
    $editable = fn ($item) => $canCreate && $item->id_empresa === ($user->isSuperAdmin() ? null : $user->id_empresa);
    $configurable = fn ($item) => $definition['scope'] === 'shared' && !$item->id_empresa && $item->estado && $user->isCompanyAdmin();
    $hasActions = $items->getCollection()->contains(fn ($item) => $editable($item) || $configurable($item));
@endphp
<div class="space-y-5">
    <x-ui.back-link :href="route('maestros.index')" label="Volver a maestros" />
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3"><h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $definition['label'] }}</h1>@if(!$canCreate)<span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-ui.icon name="lock" class="h-3.5 w-3.5" />Solo lectura</span>@endif</div>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $definition['description'] }}</p>
            @if(!$canCreate)<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $definition['scope'] === 'company' ? 'Este catálogo lo administra cada empresa.' : 'Las opciones globales las administra el superadministrador.' }}</p>@endif
        </div>
        @if($canCreate)<a wire:navigate href="{{ route('maestros.create', $catalogType) }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700"><x-ui.icon name="plus" class="h-4 w-4" />Agregar opción</a>@endif
    </div>
    <x-ui.card>
        <form wire:submit="filtrar" class="flex flex-col gap-3 sm:flex-row sm:items-start">
            <div data-search-field class="w-full sm:max-w-lg"><label for="q" class="sr-only">Buscar opciones</label><div class="relative"><span class="pointer-events-none absolute left-3 top-0 flex h-11 items-center text-slate-400"><x-ui.icon name="search" class="h-4 w-4" /></span><input id="q" name="q" wire:model="q" type="search" data-search-input placeholder="Buscar por nombre" class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></div></div>
            <div class="flex gap-2"><x-ui.button type="submit" class="h-11" wire:loading.attr="disabled" wire:target="filtrar">Buscar</x-ui.button>@if(!empty($filters['q']))<button type="button" wire:click="limpiar" class="h-11 rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-600 dark:border-slate-600 dark:text-slate-300">Limpiar</button>@endif</div>
        </form>
        <div class="mt-5 flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-700"><h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Opciones del catálogo</h2><span class="text-xs text-slate-500 dark:text-slate-400">{{ $items->total() }} {{ $items->total() === 1 ? 'opción' : 'opciones' }}</span></div>
        <table class="block w-full text-left text-sm md:table">
            <thead class="hidden text-xs uppercase text-slate-500 dark:text-slate-400 md:table-header-group"><tr><th class="py-3 pr-4">Opción</th><th class="py-3 text-right">Disponibilidad</th>@if($hasActions)<th class="py-3 pl-4 text-right">Acciones</th>@endif</tr></thead>
            <tbody class="block divide-y divide-slate-100 dark:divide-slate-700 md:table-row-group">
                @forelse($items as $item)
                    <tr wire:key="master-option-{{ $item->id }}" class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-x-3 py-4 md:table-row">
                        <td class="min-w-0 md:py-4 md:pr-4"><p class="break-words font-semibold text-slate-900 dark:text-slate-100">{{ $item->nombre }}{{ $item->codigo ? ' · '.$item->codigo : '' }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $item->id_empresa ? 'Esta empresa' : 'Catálogo global' }}{{ $item->parent ? ' · '.$item->parent->nombre : '' }}</p>@if($item->descripcion)<p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $item->descripcion }}</p>@endif</td>
                        <td class="text-right md:py-4"><x-ui.status-badge :active="$item->estado && !in_array($item->id, $disabled)" active-label="Disponible" inactive-label="Desactivada" /></td>
                        @if($hasActions)
                            <td class="col-span-2 mt-3 md:mt-0 md:py-4 md:pl-4"><div class="flex flex-wrap gap-2 md:justify-end">
                                @if($editable($item))<a wire:navigate href="{{ route('maestros.edit', [$catalogType, $item]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold dark:border-slate-600"><x-ui.icon name="pencil" class="h-3.5 w-3.5" />Editar</a>@endif
                                @if($editable($item) || $configurable($item))<form data-ajax-form method="POST" action="{{ route('maestros.toggle', [$catalogType, $item]) }}">@csrf @method('PATCH')<button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold dark:border-slate-600"><x-ui.icon name="power" class="h-3.5 w-3.5" />{{ $editable($item) ? ($item->estado ? 'Desactivar' : 'Activar') : (in_array($item->id, $disabled) ? 'Usar en mi empresa' : 'No usar en mi empresa') }}</button></form>@else<span class="text-xs text-slate-400">Solo lectura</span>@endif
                            </div></td>
                        @endif
                    </tr>
                @empty<tr class="block md:table-row"><td colspan="{{ $hasActions ? 3 : 2 }}" class="block py-10 text-center text-slate-500 dark:text-slate-400 md:table-cell">{{ !empty($filters['q']) ? 'No hay coincidencias. Prueba otro nombre o limpia la búsqueda.' : 'Todavía no hay opciones registradas en este catálogo.' }}</td></tr>@endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $items->links() }}</div>
    </x-ui.card>
</div>
