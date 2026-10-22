<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300"><x-ui.icon name="clipboard-list" class="h-5 w-5" /></div>
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Muestreos</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Selecciona un lote y da seguimiento a sus muestras de inspección.</p>
            </div>
        </div>
        @if(auth()->user()->rol?->codigo === 'INSPECTOR' && auth()->user()->hasPermission('muestreos.crear'))
            <a href="{{ route('muestreos.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                <x-ui.icon name="plus" class="h-4 w-4" /> Crear muestreo
            </a>
        @endif
    </div>

    @include('inspecciones._mensajes')

    <x-ui.card>
        <form wire:submit="buscarMuestreos" class="flex flex-wrap items-end gap-3">
            <div class="min-w-48 flex-1">
                <label for="buscar" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar por código de lote</label>
                <input data-search-input id="buscar" wire:model="buscar" type="search" placeholder="Ej. LOTE-2026-001" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
            </div>
            <x-ui.button type="submit" wire:loading.attr="disabled">
                <span class="inline-flex items-center gap-2">
                    <x-ui.icon name="search" class="h-4 w-4" />Buscar
                </span>
            </x-ui.button>
        </form>
        <x-ui.operational-filters :estados="$estados" :productos="$productos" :can-clear="$filtro !== '' || trim($buscar) !== '' || $estado !== '' || $producto !== '' || $desde !== '' || $hasta !== ''" :active="$estado !== '' || $producto !== '' || $desde !== '' || $hasta !== ''" />
    </x-ui.card>

    <x-ui.card title="Muestreos registrados" description="Muestras vinculadas con lotes de tu empresa.">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                <thead class="text-xs uppercase text-slate-500 dark:text-slate-400"><tr>
                    <th class="px-3 py-3">Muestreo</th><th class="px-3 py-3">Lote</th><th class="px-3 py-3">Productor</th><th class="px-3 py-3">Frutos</th><th class="px-3 py-3">Estado</th><th class="px-3 py-3">Acción</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($muestreos as $m)
                        <tr wire:key="muestreo-{{ $m->id_muestreo }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-3 py-3 font-semibold text-slate-900 dark:text-slate-100">#{{ $m->id_muestreo }}</td>
                            <td class="px-3 py-3">{{ $m->lote?->codigo_lote }}</td>
                            <td class="px-3 py-3">{{ $m->lote?->productor?->nombre_razon_social ?? 'Sin productor' }}</td>
                            <td class="px-3 py-3">{{ $m->tamano_muestra }}</td>
                            <td class="px-3 py-3"><x-ui.state-badge :state="$m->estado" /></td>
                            <td class="px-3 py-3"><a href="{{ route('muestreos.show', $m->id_muestreo) }}" wire:navigate class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline dark:text-brand-300">Ver detalle <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400"><x-ui.icon name="clipboard-list" class="mx-auto mb-3 h-7 w-7 text-slate-400" /><p class="font-semibold dark:text-slate-100">{{ $filtro || $estado || $producto || $desde || $hasta ? 'No hay coincidencias con estos filtros' : 'Todavía no hay muestreos registrados' }}</p><p class="mt-2">El inspector necesita un lote con recepción confirmada para iniciar la muestra.</p>@if(auth()->user()->rol?->codigo === 'INSPECTOR' && auth()->user()->hasPermission('muestreos.crear'))<a wire:navigate href="{{ route('muestreos.create') }}" class="mt-3 inline-flex font-semibold text-brand-700 dark:text-brand-300">Seleccionar lote y crear muestreo</a>@elseif(auth()->user()->hasPermission('recepciones.ver'))<a wire:navigate href="{{ route('recepciones.index') }}" class="mt-3 inline-flex font-semibold text-brand-700 dark:text-brand-300">Consultar recepciones</a>@endif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $muestreos->links() }}</div>
    </x-ui.card>
</div>
