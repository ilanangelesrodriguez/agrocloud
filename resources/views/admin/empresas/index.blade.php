<x-layouts.app title="Empresas">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700">Administración global</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-950">Empresas de AgroCloud</h2>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">Registra tenants, controla su estado y entra al contexto de usuarios de cada organización.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.export-menu :items="[
                    ['label' => 'Excel (.xlsx)', 'description' => 'Formato visual con cabeceras, filtros y estado.', 'icon' => 'file-spreadsheet', 'href' => route('admin.empresas.export.excel', request()->query())],
                    ['label' => 'CSV', 'description' => 'Datos simples para análisis o importación.', 'icon' => 'file-text', 'href' => route('admin.empresas.export.csv', request()->query())],
                    ['label' => 'Reporte / PDF', 'description' => 'Vista lista para imprimir o guardar como PDF.', 'icon' => 'printer', 'href' => route('admin.empresas.export.report', request()->query()), 'target' => '_blank'],
                ]" />
                <a href="{{ route('admin.empresas.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"><x-ui.icon name="plus" class="h-4 w-4" />Nueva empresa</a>
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
                    <div class="flex items-start justify-between"><div><p class="text-sm text-slate-500">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-semibold text-slate-950">{{ $stat['value'] }}</p></div><div class="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-ui.icon :name="$stat['icon']" class="h-5 w-5" /></div></div>
                </x-ui.card>
            @endforeach
        </section>

        <x-ui.card>
            <form method="GET" class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_220px_220px_auto_auto] lg:items-end">
                <div><label for="q" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Buscar</label><div class="relative"><x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="RUC, empresa, razón social..." class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100"></div></div>
                <div><label for="estado" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Estado</label><select id="estado" name="estado" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100"><option value="">Todos</option><option value="1" @selected(request('estado') === '1')>Activas</option><option value="0" @selected(request('estado') === '0')>Inactivas</option></select></div>
                <div><label for="tipo" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tipo</label><select id="tipo" name="tipo" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100"><option value="">Todos</option>@foreach($tipos as $tipo)<option value="{{ $tipo }}" @selected(request('tipo') === $tipo)>{{ $tipo }}</option>@endforeach</select></div>
                <x-ui.button type="submit" variant="secondary"><span class="inline-flex items-center gap-2"><x-ui.icon name="filter" class="h-4 w-4" />Filtrar</span></x-ui.button>
                @if(request()->hasAny(['q','estado','tipo']))<a href="{{ route('admin.empresas.index') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-500 hover:bg-slate-50 hover:text-slate-800">Limpiar</a>@endif
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                        <tr><th class="px-3 py-3">Empresa</th><th class="px-3 py-3">Tipo / grupo</th><th class="px-3 py-3">Usuarios</th><th class="px-3 py-3">Estado</th><th class="px-3 py-3 text-right">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($empresas as $empresa)
                            <tr class="align-middle hover:bg-slate-50/60">
                                <td class="px-3 py-4"><div class="flex items-center gap-3">@if($empresa->logo_url)<div class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5"><img src="{{ $empresa->logo_url }}" alt="" class="max-h-full max-w-full object-contain"></div>@else<div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-ui.icon name="building" class="h-5 w-5" /></div>@endif<div><p class="font-semibold text-slate-900">{{ $empresa->nombre_comercial }}</p><p class="mt-0.5 text-xs text-slate-500">RUC {{ $empresa->ruc }} · {{ $empresa->razon_social }}</p></div></div></td>
                                <td class="px-3 py-4"><p class="text-slate-700">{{ $empresa->tipo_empresa ?: 'Sin tipo' }}</p><p class="mt-0.5 text-xs text-slate-400">{{ $empresa->grupo_empresarial ?: 'Sin grupo empresarial' }}</p></td>
                                <td class="px-3 py-4"><p class="font-medium text-slate-800">{{ $empresa->usuarios_count }} usuarios</p><p class="mt-0.5 text-xs text-slate-400">{{ $empresa->administradores_count }} admin.</p></td>
                                <td class="px-3 py-4"><x-ui.status-badge :active="$empresa->estado" active-label="Activa" inactive-label="Inactiva" /></td>
                                <td class="px-3 py-4"><div class="flex justify-end gap-1.5"><a href="{{ route('admin.empresas.show', $empresa) }}" title="Ver empresa" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"><x-ui.icon name="eye" class="h-4 w-4" /></a><a href="{{ route('admin.empresas.usuarios.index', $empresa) }}" title="Usuarios" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"><x-ui.icon name="users" class="h-4 w-4" /></a><a href="{{ route('admin.empresas.edit', $empresa) }}" title="Editar" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"><x-ui.icon name="pencil" class="h-4 w-4" /></a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-12 text-center"><div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400"><x-ui.icon name="building" class="h-6 w-6" /></div><p class="mt-3 font-medium text-slate-700">No se encontraron empresas</p><p class="mt-1 text-sm text-slate-400">Prueba otros filtros o registra una nueva empresa.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $empresas->links() }}</div>
        </x-ui.card>
    </div>
</x-layouts.app>
