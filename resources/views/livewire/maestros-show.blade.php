@php
        $user = auth()->user();
        $canCreate = $definition['scope'] === 'shared' || ($definition['scope'] === 'global' && $user->isSuperAdmin()) || ($definition['scope'] === 'company' && $user->isCompanyAdmin());
    @endphp
    <div class="space-y-5">
        <x-ui.back-link :href="route('maestros.index')" label="Volver a maestros" />
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div><h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $definition['label'] }}</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $definition['description'] }}</p></div>
            @if ($canCreate)<a wire:navigate href="{{ route('maestros.create', $catalogType) }}" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">+ Agregar opción</a>@endif
        </div>
        @if (!$canCreate)<x-ui.alert>Consulta de solo lectura. {{ $definition['scope'] === 'company' ? 'Estas opciones las administra cada empresa desde su cuenta de administrador.' : 'Las opciones globales las administra el superadministrador.' }}</x-ui.alert>@endif
        <form wire:submit="filtrar" class="flex items-start gap-3">
            <div class="w-full max-w-lg"><label for="q" class="sr-only">Buscar opciones</label><input id="q" name="q" wire:model="q" type="search" data-search-input  placeholder="Buscar por nombre" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></div>
            <x-ui.button type="submit" class="h-11">Buscar</x-ui.button>
            @if(!empty($filters['q']))<button type="button" wire:click="limpiar" class="px-3 py-3 text-sm text-slate-500">Limpiar</button>@endif
        </form>
        <x-ui.card>
            <div class="overflow-x-auto"><table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500 dark:border-slate-700"><tr><th class="p-3">Opción</th><th class="p-3">Origen</th><th class="p-3">Disponibilidad</th><th class="p-3 text-right">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($items as $item)
                        @php($canEdit = $canCreate && $item->id_empresa === ($user->isSuperAdmin() ? null : $user->id_empresa))
                        <tr>
                            <td class="p-3"><p class="font-semibold text-slate-900 dark:text-slate-100">{{ $item->nombre }}{{ $item->codigo ? ' · '.$item->codigo : '' }}</p>@if($item->parent)<p class="mt-1 text-xs text-slate-500">Producto: {{ $item->parent->nombre }}</p>@endif<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $item->descripcion }}</p></td>
                            <td class="p-3 text-slate-500 dark:text-slate-400">{{ $item->id_empresa ? 'Esta empresa' : 'Global' }}</td>
                            <td class="p-3"><x-ui.status-badge :active="$item->estado && !in_array($item->id, $disabled)" active-label="Disponible" inactive-label="Desactivada" /></td>
                            <td class="p-3"><div class="flex justify-end gap-2">
                                @if($canEdit)<a wire:navigate href="{{ route('maestros.edit', [$catalogType, $item]) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold dark:border-slate-600">Editar</a>@endif
                                @if($canEdit || ($definition['scope'] === 'shared' && !$item->id_empresa && $item->estado && $user->isCompanyAdmin()))
                                    <form data-ajax-form method="POST" action="{{ route('maestros.toggle', [$catalogType, $item]) }}">@csrf @method('PATCH')<button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold dark:border-slate-600">{{ $canEdit ? ($item->estado ? 'Desactivar' : 'Activar') : (in_array($item->id, $disabled) ? 'Usar en mi empresa' : 'No usar en mi empresa') }}</button></form>
                                @endif
                            </div></td>
                        </tr>
                    @empty<tr><td colspan="4" class="p-8 text-center text-slate-500 dark:text-slate-400">{{ !empty($filters['q']) ? 'No hay opciones que coincidan con tu búsqueda.' : 'No hay opciones registradas. Agrega las que utiliza tu empresa.' }}</td></tr>@endforelse
                </tbody>
            </table></div>
            <div class="mt-4">{{ $items->links() }}</div>
        </x-ui.card>
    </div>
