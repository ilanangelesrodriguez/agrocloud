<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Lotes</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Consulta y administra los lotes registrados en tu empresa.</p>
        </div>
        @if (auth()->user()->hasPermission('lotes.crear'))
            <a href="{{ route('lotes.create') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                <x-ui.icon name="plus" class="h-4 w-4" /><span>Nuevo lote</span>
            </a>
        @endif
    </div>


    <x-ui.card>
        <form wire:submit="buscarLotes" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="buscar-lote" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar lote</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5"><x-ui.icon name="search" class="h-4 w-4 text-slate-400" /></div>
                    <input data-search-input id="buscar-lote" wire:model="buscar" type="search" placeholder="Código, producto, variedad, productor o RUC..." class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3.5 text-sm text-slate-900 outline-none transition dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                </div>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="submit" wire:loading.attr="disabled"><x-ui.icon name="search" class="mr-2 h-4 w-4" />Buscar</x-ui.button>
            </div>
        </form>
        <x-ui.operational-filters :estados="$estados" :productos="$productos" :can-clear="$filtro !== '' || trim($buscar) !== '' || $estado !== '' || $producto !== '' || $desde !== '' || $hasta !== ''" :active="$estado !== '' || $producto !== '' || $desde !== '' || $hasta !== ''" />
    </x-ui.card>

    <x-ui.card>
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Listado de lotes</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $lotes->total() }} {{ $lotes->total() === 1 ? 'lote registrado' : 'lotes registrados' }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    <th class="px-4 py-3">Lote / producto</th><th class="px-4 py-3">Productor</th><th class="px-4 py-3 text-right">Peso</th><th class="px-4 py-3">Fecha de ingreso</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3 text-right">Acciones</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-700 dark:bg-slate-900">
                    @forelse ($lotes as $lote)
                        @php($estado = $lote->estado instanceof \BackedEnum ? $lote->estado->value : $lote->estado)
                        <tr wire:key="lote-{{ $lote->id_lote }}" class="transition hover:bg-slate-50 dark:hover:bg-slate-700">
                            <td class="whitespace-nowrap px-4 py-4"><div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $lote->codigo_lote }}</div><div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $lote->producto }} {{ $lote->variedad }}</div></td>
                            <td class="px-4 py-4">@if ($lote->productor)<div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $lote->productor->nombre_razon_social }}</div><div class="mt-1 text-xs text-slate-500 dark:text-slate-400">RUC: {{ $lote->productor->ruc }}</div>@else<span class="text-sm text-slate-400">Sin productor</span>@endif</td>
                            <td class="whitespace-nowrap px-4 py-4 text-right"><span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ number_format((float) $lote->peso_operativo_kg, 2) }}</span> <span class="text-xs text-slate-500 dark:text-slate-400">kg</span><p class="mt-1 text-xs text-slate-500">{{ $lote->recepcion ? 'Recibido' : 'Declarado' }}</p></td>
                            <td class="whitespace-nowrap px-4 py-4"><div class="text-sm text-slate-700 dark:text-slate-200">{{ $lote->fecha_recepcion?->format('d/m/Y') }}</div><div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $lote->recepcion ? 'Confirmada' : 'Prevista' }} · {{ $lote->fecha_recepcion?->format('H:i') }}</div></td>
                            <td class="whitespace-nowrap px-4 py-4"><x-ui.state-badge :state="$estado" /></td>
                            <td class="whitespace-nowrap px-4 py-4"><div class="flex justify-end gap-2">
                                @if (auth()->user()->hasPermission('lotes.ver'))<a href="{{ route('lotes.show', $lote->id_lote) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"><x-ui.icon name="eye" class="h-4 w-4" />Ver</a>@endif
                                @if (auth()->user()->hasPermission('lotes.editar') && !$lote->recepcion_exists && !$lote->muestreos_exists)<a href="{{ route('lotes.edit', $lote->id_lote) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"><x-ui.icon name="pencil" class="h-4 w-4" />Editar</a>@endif
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400"><x-ui.icon name="briefcase" class="mx-auto h-6 w-6 text-slate-400" /><h3 class="mt-4 font-semibold text-slate-900 dark:text-slate-100">{{ $filtro || $estado || $producto || $desde || $hasta ? 'Ningún lote coincide con los filtros' : 'Aún no hay lotes registrados' }}</h3><p class="mt-1">{{ $filtro || $estado || $producto || $desde || $hasta ? 'Limpia los filtros o cambia la búsqueda para consultar otros lotes.' : 'Registra un lote para preparar su recepción física.' }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($lotes->hasPages())<div class="mt-5 border-t border-slate-100 pt-5 dark:border-slate-700">{{ $lotes->links() }}</div>@endif
    </x-ui.card>
</div>
