<x-layouts.app title="Dashboard">
    @if ($user?->isSuperAdmin())
        <div class="space-y-6">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-brand-900 p-6 text-white shadow-sm sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-brand-100 ring-1 ring-white/10">
                            <x-ui.icon name="shield" class="h-4 w-4" /> Administración global
                        </div>
                        <h2 class="mt-4 text-2xl font-semibold sm:text-3xl">Bienvenido, {{ $user->nombre }}</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Gestiona empresas, usuarios por tenant y la seguridad global de AgroCloud AI sin pertenecer a una empresa específica.</p>
                    </div>
                    <a wire:navigate href="{{ route('admin.empresas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-900 dark:text-slate-100 shadow-sm transition hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-ui.icon name="building-plus" class="h-4 w-4" /> Gestionar empresas
                    </a>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Empresas', 'value' => $globalStats['empresas'], 'icon' => 'building'],
                    ['label' => 'Empresas activas', 'value' => $globalStats['empresas_activas'], 'icon' => 'power'],
                    ['label' => 'Usuarios empresariales', 'value' => $globalStats['usuarios'], 'icon' => 'users'],
                    ['label' => 'Administradores', 'value' => $globalStats['administradores'], 'icon' => 'shield'],
                ] as $metric)
                    <x-ui.card>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</p>
                                <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 dark:text-slate-100">{{ $metric['value'] }}</p>
                            </div>
                            <div class="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 dark:bg-brand-900/35 text-brand-700 dark:text-brand-300"><x-ui.icon :name="$metric['icon']" class="h-5 w-5" /></div>
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
