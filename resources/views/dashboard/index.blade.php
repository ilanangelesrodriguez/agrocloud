<x-layouts.app title="Dashboard">
    @if ($user?->isSuperAdmin())
        <div class="space-y-6">
            <div class="flex flex-col gap-4 rounded-2xl border border-brand-100 bg-brand-50/60 px-5 py-5 sm:flex-row sm:items-end sm:justify-between dark:border-brand-900/50 dark:bg-brand-950/20">
                <div class="border-l-4 border-brand-500 pl-4">
                    <p class="text-sm font-medium text-brand-700 dark:text-brand-300">Administración global</p>
                    <h1 class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">Bienvenido, {{ $user->nombre }}</h1>
                    <p class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400">Consulta el estado general de AgroCloud y accede rápidamente a la administración de la plataforma.</p>
                </div>
                <a wire:navigate href="{{ route('admin.empresas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200 dark:focus:ring-brand-900/50">
                    <x-ui.icon name="building-plus" class="h-4 w-4" /> Gestionar empresas
                </a>
            </div>

            <section aria-label="Indicadores globales" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Empresas', 'value' => $globalStats['empresas'], 'icon' => 'building', 'tone' => 'brand'],
                    ['label' => 'Empresas activas', 'value' => $globalStats['empresas_activas'], 'icon' => 'power', 'tone' => 'emerald'],
                    ['label' => 'Usuarios empresariales', 'value' => $globalStats['usuarios'], 'icon' => 'users', 'tone' => 'sky'],
                    ['label' => 'Administradores', 'value' => $globalStats['administradores'], 'icon' => 'shield', 'tone' => 'violet'],
                    ['label' => 'Recepciones de hoy (global)', 'value' => $globalStats['recepciones_hoy'], 'icon' => 'briefcase', 'tone' => 'amber'],
                    ['label' => 'Lotes en operación (global)', 'value' => $globalStats['lotes_activos'], 'icon' => 'clipboard-list', 'tone' => 'orange'],
                    ['label' => 'Inspecciones en captura (global)', 'value' => $globalStats['inspecciones_pendientes'], 'icon' => 'camera', 'tone' => 'cyan'],
                    ['label' => 'Dictámenes pendientes (global)', 'value' => $globalStats['dictamenes_pendientes'], 'icon' => 'shield', 'tone' => 'rose'],
                ] as $metric)
                    <x-ui.card class="relative overflow-hidden bg-white dark:bg-slate-900">
                        <div class="absolute inset-x-0 top-0 h-1 {{ match ($metric['tone']) {
                            'emerald' => 'bg-emerald-500',
                            'sky' => 'bg-sky-500',
                            'violet' => 'bg-violet-500',
                            'amber' => 'bg-amber-500',
                            'orange' => 'bg-orange-500',
                            'cyan' => 'bg-cyan-500',
                            'rose' => 'bg-rose-500',
                            default => 'bg-brand-500',
                        } }}"></div>
                        <div class="flex items-center gap-3">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ match ($metric['tone']) {
                                'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/35 dark:text-emerald-300',
                                'sky' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/35 dark:text-sky-300',
                                'violet' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/35 dark:text-violet-300',
                                'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/35 dark:text-amber-300',
                                'orange' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/35 dark:text-orange-300',
                                'cyan' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/35 dark:text-cyan-300',
                                'rose' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/35 dark:text-rose-300',
                                default => 'bg-brand-100 text-brand-700 dark:bg-brand-900/35 dark:text-brand-300',
                            } }}"><x-ui.icon :name="$metric['icon']" class="h-5 w-5" /></div>
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</p>
                                <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-100">{{ number_format($metric['value']) }}</p>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </section>

            <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                <x-ui.card title="Empresas recientes" description="Últimos tenants registrados en la plataforma.">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400"><tr><th class="px-2 py-3">Empresa</th><th class="px-2 py-3">RUC</th><th class="px-2 py-3">Usuarios</th><th class="px-2 py-3">Estado</th><th class="px-2 py-3"></th></tr></thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                @forelse ($recentCompanies as $empresa)
                                    <tr>
                                        <td class="px-2 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $empresa->nombre_comercial }}</td>
                                        <td class="px-2 py-3 text-slate-500 dark:text-slate-400">{{ $empresa->ruc }}</td>
                                        <td class="px-2 py-3 text-slate-500 dark:text-slate-400">{{ $empresa->usuarios_count }}</td>
                                        <td class="px-2 py-3"><x-ui.status-badge :active="$empresa->estado" active-label="Activa" inactive-label="Inactiva" /></td>
                                        <td class="px-2 py-3 text-right"><a wire:navigate href="{{ route('admin.empresas.show', $empresa) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 dark:text-brand-300 hover:text-brand-800 dark:hover:text-brand-200">Ver <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-2 py-8 text-center text-slate-500 dark:text-slate-400">Aún no hay empresas registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                <x-ui.card title="Accesos de plataforma" description="Herramientas reservadas al SUPERADMIN.">
                    <div class="space-y-2">
                        <a wire:navigate href="{{ route('admin.empresas.index') }}" class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-700 p-3.5 transition hover:border-brand-200 dark:hover:border-brand-800 hover:bg-brand-50/50 dark:hover:bg-brand-900/35"><span class="flex items-center gap-3 text-sm font-medium text-slate-800 dark:text-slate-100"><x-ui.icon name="building-plus" class="h-5 w-5 text-brand-700 dark:text-brand-300" />Empresas</span><x-ui.icon name="chevron-right" class="h-4 w-4 text-slate-400" /></a>
                        <a wire:navigate href="{{ route('roles.index') }}" class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-700 p-3.5 transition hover:border-brand-200 dark:hover:border-brand-800 hover:bg-brand-50/50 dark:hover:bg-brand-900/35"><span class="flex items-center gap-3 text-sm font-medium text-slate-800 dark:text-slate-100"><x-ui.icon name="shield" class="h-5 w-5 text-brand-700 dark:text-brand-300" />Roles</span><x-ui.icon name="chevron-right" class="h-4 w-4 text-slate-400" /></a>
                        <a wire:navigate href="{{ route('permisos.index') }}" class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-700 p-3.5 transition hover:border-brand-200 dark:hover:border-brand-800 hover:bg-brand-50/50 dark:hover:bg-brand-900/35"><span class="flex items-center gap-3 text-sm font-medium text-slate-800 dark:text-slate-100"><x-ui.icon name="key" class="h-5 w-5 text-brand-700 dark:text-brand-300" />Permisos</span><x-ui.icon name="chevron-right" class="h-4 w-4 text-slate-400" /></a>
                    </div>
                </x-ui.card>
            </div>
        </div>
    @else
        @include('dashboard.company')
    @endif
</x-layouts.app>
