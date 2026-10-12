<div class="space-y-6">
    <section class="rounded-2xl bg-gradient-to-br from-brand-700 to-brand-600 p-6 text-white sm:p-7">
        <p class="text-sm font-medium text-brand-100">{{ $user->empresa?->nombre_comercial }} · {{ $user->rol?->nombre }}</p>
        <h2 class="mt-2 text-2xl font-semibold sm:text-3xl">Bienvenido, {{ $user->nombre }}</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-100">{{ $roleDescription }}</p>
        <div class="mt-5 flex flex-wrap gap-2">
            @foreach ($quickActions as $action)
                <a wire:navigate href="{{ route($action['route']) }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-3.5 py-2 text-sm font-semibold ring-1 ring-white/20 hover:bg-white/20">{{ $action['label'] }} <x-ui.icon name="chevron-right" class="h-4 w-4" /></a>
            @endforeach
        </div>
    </section>

    @if ($metrics)
        <section aria-label="Resumen de la empresa" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <x-ui.card>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-slate-100">{{ number_format($metric['value']) }}</p>
                    @if ($metric['route'])
                        <a wire:navigate href="{{ route($metric['route']) }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:underline dark:text-brand-300">Ver registros <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /></a>
                    @endif
                </x-ui.card>
            @endforeach
        </section>
    @endif

    @if (!$metrics && $user->empresa && $user->hasPermission('empresa.ver'))
        <x-ui.card title="Tu empresa" description="Información de la empresa con la que trabajas.">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['ruc' => 'RUC', 'direccion' => 'Dirección', 'telefono' => 'Teléfono', 'correo' => 'Correo'] as $field => $label)
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</dt><dd class="mt-1 break-words text-sm font-medium text-slate-900 dark:text-slate-100">{{ $user->empresa->$field ?: 'No registrado' }}</dd></div>
                @endforeach
            </dl>
        </x-ui.card>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        @if ($user->hasPermission('inspecciones.ver'))
            <x-ui.card :title="$user->rol?->codigo === 'SUPERVISOR' ? 'Expedientes pendientes de dictamen' : ($user->rol?->codigo === 'INSPECTOR' ? 'Tus inspecciones y las disponibles' : 'Inspecciones recientes')">
                <div class="space-y-3">
                    @forelse ($recentInspections as $inspection)
                        <a wire:navigate href="{{ route($user->rol?->codigo === 'SUPERVISOR' && $user->hasPermission('calidad.ver') ? 'calidad.show' : 'inspecciones.show', $inspection->id_inspeccion) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3 hover:border-brand-400 dark:border-slate-700">
                            <div><p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Inspección #{{ $inspection->id_inspeccion }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $inspection->muestreo?->lote?->codigo_lote }}</p></div>
                            <x-ui.state-badge :state="$inspection->estado" />
                        </a>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">No hay inspecciones para mostrar en esta etapa.</p>
                    @endforelse
                </div>
            </x-ui.card>
        @endif
        @if ($user->hasPermission('lotes.ver'))
            <x-ui.card title="Últimos lotes recibidos">
                <div class="space-y-3">
                    @forelse ($recentLots as $lot)
                        <a wire:navigate href="{{ route('lotes.show', $lot->id_lote) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3 hover:border-brand-400 dark:border-slate-700">
                            <div><p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $lot->codigo_lote }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $lot->producto }} · {{ $lot->productor?->nombre_razon_social }}</p></div>
                            <x-ui.state-badge :state="$lot->estado" />
                        </a>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500 dark:text-slate-400">Todavía no hay lotes registrados.</p>
                    @endforelse
                </div>
            </x-ui.card>
        @endif
    </div>
    @if (!$user->id_empresa)
        <x-ui.alert>Tu cuenta todavía no tiene una empresa asignada. Contacta al administrador para habilitar tus módulos.</x-ui.alert>
    @elseif (!$metrics && !$quickActions)
        <x-ui.alert>No tienes módulos operativos habilitados. Puedes revisar tu <a wire:navigate href="{{ route('perfil.edit') }}" class="font-semibold underline">perfil</a> o consultar al administrador de tu empresa.</x-ui.alert>
    @endif
</div>
