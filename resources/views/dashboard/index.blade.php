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
                    <a href="{{ route('admin.empresas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-slate-100">
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
                                <p class="text-sm text-slate-500">{{ $metric['label'] }}</p>
                                <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $metric['value'] }}</p>
                            </div>
                            <div class="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-ui.icon :name="$metric['icon']" class="h-5 w-5" /></div>
                        </div>
                    </x-ui.card>
                @endforeach
            </section>

            <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                <x-ui.card title="Empresas recientes" description="Últimos tenants registrados en la plataforma.">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-2 py-3">Empresa</th><th class="px-2 py-3">RUC</th><th class="px-2 py-3">Usuarios</th><th class="px-2 py-3">Estado</th><th class="px-2 py-3"></th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($recentCompanies as $empresa)
                                    <tr>
                                        <td class="px-2 py-3 font-medium text-slate-900">{{ $empresa->nombre_comercial }}</td>
                                        <td class="px-2 py-3 text-slate-500">{{ $empresa->ruc }}</td>
                                        <td class="px-2 py-3 text-slate-500">{{ $empresa->usuarios_count }}</td>
                                        <td class="px-2 py-3"><x-ui.status-badge :active="$empresa->estado" active-label="Activa" inactive-label="Inactiva" /></td>
                                        <td class="px-2 py-3 text-right"><a href="{{ route('admin.empresas.show', $empresa) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 hover:text-brand-800">Ver <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-2 py-8 text-center text-slate-500">Aún no hay empresas registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                <x-ui.card title="Accesos de plataforma" description="Herramientas reservadas al SUPERADMIN.">
                    <div class="space-y-2">
                        <a href="{{ route('admin.empresas.index') }}" class="flex items-center justify-between rounded-xl border border-slate-200 p-3.5 transition hover:border-brand-200 hover:bg-brand-50/50"><span class="flex items-center gap-3 text-sm font-medium text-slate-800"><x-ui.icon name="building-plus" class="h-5 w-5 text-brand-700" />Empresas</span><x-ui.icon name="chevron-right" class="h-4 w-4 text-slate-400" /></a>
                        <a href="{{ route('roles.index') }}" class="flex items-center justify-between rounded-xl border border-slate-200 p-3.5 transition hover:border-brand-200 hover:bg-brand-50/50"><span class="flex items-center gap-3 text-sm font-medium text-slate-800"><x-ui.icon name="shield" class="h-5 w-5 text-brand-700" />Roles</span><x-ui.icon name="chevron-right" class="h-4 w-4 text-slate-400" /></a>
                        <a href="{{ route('permisos.index') }}" class="flex items-center justify-between rounded-xl border border-slate-200 p-3.5 transition hover:border-brand-200 hover:bg-brand-50/50"><span class="flex items-center gap-3 text-sm font-medium text-slate-800"><x-ui.icon name="key" class="h-5 w-5 text-brand-700" />Permisos</span><x-ui.icon name="chevron-right" class="h-4 w-4 text-slate-400" /></a>
                    </div>
                </x-ui.card>
            </div>
        </div>
    @else
        <div class="space-y-6">
            <section class="rounded-2xl bg-gradient-to-br from-brand-700 to-brand-600 p-6 text-white shadow-sm sm:p-7">
                <p class="text-sm font-medium text-brand-100">{{ $user?->empresa?->nombre_comercial ?? 'AgroCloud AI' }}</p>
                <h2 class="mt-2 text-2xl font-semibold sm:text-3xl">Bienvenido, {{ $user?->nombre ?? 'Usuario' }}</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-100">Trabajas con tu cuenta personal dentro del contexto de empresa asignado. Tu rol determina los módulos y acciones disponibles.</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('perfil.edit') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-3.5 py-2 text-sm font-semibold text-white ring-1 ring-white/20 hover:bg-white/15"><x-ui.icon name="user" class="h-4 w-4" />Mi perfil</a>
                    @if ($user?->empresa && $user->hasPermission('empresa.ver'))<a href="{{ route('empresa.edit') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-3.5 py-2 text-sm font-semibold text-white ring-1 ring-white/20 hover:bg-white/15"><x-ui.icon name="building" class="h-4 w-4" />Mi empresa</a>@endif
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([['label' => 'Lotes activos', 'value' => 0],['label' => 'Inspecciones pendientes', 'value' => 0],['label' => 'Incidencias abiertas', 'value' => 0],['label' => 'Detecciones IA', 'value' => 0]] as $metric)
                    <x-ui.card><p class="text-sm text-slate-500">{{ $metric['label'] }}</p><p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $metric['value'] }}</p></x-ui.card>
                @endforeach
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-ui.card title="Tu acceso" description="Identidad, empresa y rol de esta sesión.">
                    <dl class="space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3"><dt class="text-slate-500">Usuario</dt><dd class="font-medium text-slate-900">{{ $user?->nombre_completo }}</dd></div>
                        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3"><dt class="text-slate-500">Empresa</dt><dd class="font-medium text-slate-900">{{ $user?->empresa?->nombre_comercial ?? 'Administración global' }}</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-slate-500">Rol</dt><dd class="font-medium text-slate-900">{{ $user?->rol?->nombre ?? 'Sin rol asignado' }}</dd></div>
                    </dl>
                </x-ui.card>
                <x-ui.card title="Regla de acceso" description="Base multiempresa de AgroCloud AI."><div class="space-y-3 text-sm text-slate-600"><p><strong class="text-slate-900">1.</strong> Inicias sesión como usuario, no como empresa.</p><p><strong class="text-slate-900">2.</strong> La empresa se determina por tu cuenta.</p><p><strong class="text-slate-900">3.</strong> ADMIN administra solo su empresa.</p><p><strong class="text-slate-900">4.</strong> SUPERADMIN administra la plataforma.</p></div></x-ui.card>
            </div>
        </div>
    @endif
</x-layouts.app>
