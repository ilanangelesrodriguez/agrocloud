<div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">Administración global</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-slate-100">Empresas de AgroCloud</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">Gestiona empresas y sus usuarios. Busca por nombre o RUC y combina los filtros que necesites.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.export-menu :items="[
                    ['label' => 'Excel (.xlsx)', 'description' => 'Formato visual con cabeceras, filtros y estado.', 'icon' => 'file-spreadsheet', 'href' => route('admin.empresas.export.excel', [])],
                    ['label' => 'CSV', 'description' => 'Datos simples para análisis o importación.', 'icon' => 'file-text', 'href' => route('admin.empresas.export.csv', [])],
                    ['label' => 'Reporte / PDF', 'description' => 'Vista lista para imprimir o guardar como PDF.', 'icon' => 'printer', 'href' => route('admin.empresas.export.report', []), 'target' => '_blank'],
                ]" />
                <a wire:navigate href="{{ route('admin.empresas.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"><x-ui.icon name="plus" class="h-4 w-4" />Nueva empresa</a>
            </div>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Empresas', 'value' => $stats['total'], 'icon' => 'building'],
                ['label' => 'Activas', 'value' => $stats['activas'], 'icon' => 'power'],
                ['label' => 'Inactivas', 'value' => $stats['inactivas'], 'icon' => 'lock'],
                ['label' => 'Usuarios', 'value' => $stats['usuarios'], 'icon' => 'users'],
            ] as $stat)
                <x-ui.card>
                    <div class="flex items-start justify-between"><div><p class="text-sm text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-slate-100">{{ $stat['value'] }}</p></div><div class="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 dark:bg-brand-900/35 text-brand-700 dark:text-brand-300"><x-ui.icon :name="$stat['icon']" class="h-5 w-5" /></div></div>
                </x-ui.card>
            @endforeach
        </section>

        <x-ui.card>
            <form wire:submit="filtrar" class="grid gap-4 md:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_minmax(150px,1fr)_minmax(180px,1fr)_auto]">
                <div data-search-field class="min-w-0 md:col-span-2 xl:col-span-1">
                    <label for="q" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Buscar empresa</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-0 flex h-11 items-center text-slate-400"><x-ui.icon name="search" class="h-4 w-4" /></span>
                        <input data-search-input id="q" name="q" wire:model="q" type="search" placeholder="Nombre, razón social o RUC" class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50">
                    </div>
                    @error('q')<p class="mt-1 text-xs text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="estado" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
                    <select id="estado" name="estado" wire:model="estado" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"><option value="">Todas</option><option value="1">Activas</option><option value="0">Inactivas</option></select>
                </div>
                <div>
                    <label for="tipo" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo de empresa</label>
                    <select id="tipo" name="tipo" wire:model="tipo" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"><option value="">Todos los tipos</option>@foreach($tipos as $tipoOption)<option value="{{ $tipoOption }}">{{ $tipoOption }}</option>@endforeach</select>
                </div>
                <div class="flex items-start gap-2 md:col-span-2 xl:col-span-1 xl:pt-7">
                    <x-ui.button type="submit" class="h-11" wire:loading.attr="disabled" wire:target="filtrar"><x-ui.icon name="filter" class="mr-2 h-4 w-4" /><span wire:loading.remove wire:target="filtrar">Aplicar filtros</span><span wire:loading wire:target="filtrar">Filtrando…</span></x-ui.button>
                    @if($filters)<button type="button" wire:click="limpiar" class="inline-flex h-11 items-center justify-center gap-1.5 rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">Limpiar</button>@endif
                </div>
            </form>
            @if($filters)
                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 text-xs text-slate-600 dark:border-slate-700 dark:text-slate-300" role="status">
                    <span class="font-medium">Filtros aplicados:</span>
                    @if(!empty($filters['q']))<span class="rounded-lg bg-slate-100 px-2 py-1 dark:bg-slate-800">{{ $filters['q'] }}</span>@endif
                    @if(isset($filters['estado']) && $filters['estado'] !== '')<span class="rounded-lg bg-slate-100 px-2 py-1 dark:bg-slate-800">{{ $filters['estado'] === '1' ? 'Activas' : 'Inactivas' }}</span>@endif
                    @if(!empty($filters['tipo']))<span class="rounded-lg bg-slate-100 px-2 py-1 dark:bg-slate-800">{{ $filters['tipo'] }}</span>@endif
                </div>
            @endif
        </x-ui.card>

        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <tr><th class="px-3 py-3">Empresa</th><th class="px-3 py-3">Tipo / grupo</th><th class="px-3 py-3">Usuarios</th><th class="px-3 py-3">Estado</th><th class="px-3 py-3 text-right">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($empresas as $empresa)
                            <tr wire:key="empresa-{{ $empresa->id_empresa }}" class="align-middle hover:bg-slate-50/60 dark:hover:bg-slate-700/60">
                                <td class="px-3 py-4"><div class="flex items-center gap-3">@if($empresa->logo_url)<div class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-1.5"><img src="{{ $empresa->logo_url }}" alt="" class="max-h-full max-w-full object-contain"></div>@else<div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-50 dark:bg-brand-900/35 text-brand-700 dark:text-brand-300"><x-ui.icon name="building" class="h-5 w-5" /></div>@endif<div><p class="font-semibold text-slate-900 dark:text-slate-100">{{ $empresa->nombre_comercial }}</p><p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">RUC {{ $empresa->ruc }} · {{ $empresa->razon_social }}</p></div></div></td>
                                <td class="px-3 py-4"><p class="text-slate-700 dark:text-slate-200">{{ $empresa->tipo_empresa ?: 'Sin tipo' }}</p><p class="mt-0.5 text-xs text-slate-400">{{ $empresa->grupo_empresarial ?: 'Sin grupo empresarial' }}</p></td>
                                <td class="px-3 py-4"><p class="font-medium text-slate-800 dark:text-slate-100">{{ $empresa->usuarios_count }} usuarios</p><p class="mt-0.5 text-xs text-slate-400">{{ $empresa->administradores_count }} admin.</p></td>
                                <td class="px-3 py-4"><x-ui.status-badge :active="$empresa->estado" active-label="Activa" inactive-label="Inactiva" /></td>
                                <td class="px-3 py-4"><div class="flex justify-end gap-1.5"><a wire:navigate href="{{ route('admin.empresas.show', $empresa) }}" title="Ver empresa" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 transition hover:border-brand-200 dark:hover:border-brand-800 hover:bg-brand-50 dark:hover:bg-brand-900/35 hover:text-brand-700 dark:hover:text-brand-300"><x-ui.icon name="eye" class="h-4 w-4" /></a><a wire:navigate href="{{ route('admin.empresas.usuarios.index', $empresa) }}" title="Usuarios" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 transition hover:border-brand-200 dark:hover:border-brand-800 hover:bg-brand-50 dark:hover:bg-brand-900/35 hover:text-brand-700 dark:hover:text-brand-300"><x-ui.icon name="users" class="h-4 w-4" /></a><a wire:navigate href="{{ route('admin.empresas.edit', $empresa) }}" title="Editar" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 transition hover:border-brand-200 dark:hover:border-brand-800 hover:bg-brand-50 dark:hover:bg-brand-900/35 hover:text-brand-700 dark:hover:text-brand-300"><x-ui.icon name="pencil" class="h-4 w-4" /></a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-12 text-center"><div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400"><x-ui.icon name="building" class="h-6 w-6" /></div><p class="mt-3 font-medium text-slate-700 dark:text-slate-200">No se encontraron empresas</p><p class="mt-1 text-sm text-slate-400">Prueba otros filtros o registra una nueva empresa.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $empresas->links() }}</div>
        </x-ui.card>
    </div>
