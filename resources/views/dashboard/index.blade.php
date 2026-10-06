<x-layouts.app title="Dashboard">
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
            <x-ui.card title="Regla de acceso" description="Base multiempresa de AgroCloud AI."><div class="space-y-3 text-sm text-slate-600"><p><strong class="text-slate-900">1.</strong> Inicias sesión como usuario, no como empresa.</p><p><strong class="text-slate-900">2.</strong> La empresa se determina por tu cuenta.</p><p><strong class="text-slate-900">3.</strong> ADMIN administra solo su empresa.</p><p><strong class="text-slate-900">4.</strong> SUPERADMIN queda reservado para administración global.</p></div></x-ui.card>
        </div>
    </div>
</x-layouts.app>
