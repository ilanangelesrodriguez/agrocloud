<div class="space-y-6">
    <div class="relative isolate overflow-hidden rounded-2xl bg-brand-700 px-5 py-6 text-white shadow-sm sm:px-7 sm:py-7">
        <div class="pointer-events-none absolute -right-10 -top-16 h-48 w-48 rounded-full bg-brand-500/40"></div>
        <div class="pointer-events-none absolute -bottom-24 right-24 h-40 w-40 rounded-full bg-brand-800/40"></div>
        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="border-l-4 border-brand-200 pl-4">
                <p class="text-sm font-medium text-brand-100">{{ $user->empresa?->nombre_comercial }} · {{ $user->rol?->nombre }}</p>
                <h1 class="mt-1 text-2xl font-semibold text-white">Bienvenido, {{ $user->nombre }}</h1>
                <p class="mt-1 max-w-2xl text-sm leading-6 text-brand-100">{{ $roleDescription }}</p>
            </div>
            @if ($quickActions)
                <a wire:navigate href="{{ route($quickActions[0]['route']) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50 focus:outline-none focus:ring-4 focus:ring-white/40">
                    {{ $quickActions[0]['label'] }}
                    <x-ui.icon name="chevron-right" class="h-4 w-4" />
                </a>
            @endif
        </div>
    </div>

    @if ($quickActions)
        <x-ui.card title="Accesos frecuentes" description="Continúa rápidamente con las tareas disponibles para tu cuenta.">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($quickActions as $action)
                    <a wire:navigate href="{{ route($action['route']) }}" class="group flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 text-sm font-medium text-slate-800 transition hover:border-brand-300 hover:bg-brand-50 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 dark:hover:border-brand-800 dark:hover:bg-brand-900/25">
                        <span class="flex min-w-0 items-center gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                                <x-ui.icon name="chevron-right" class="h-4 w-4" />
                            </span>
                            <span class="truncate">{{ $action['label'] }}</span>
                        </span>
                        <x-ui.icon name="chevron-right" class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-brand-700 dark:text-slate-500 dark:group-hover:text-brand-300" />
                    </a>
                @endforeach
            </div>
        </x-ui.card>
    @endif

    @if ($operationalSummary)
        <section aria-label="Resumen operativo" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($operationalSummary as $label => $value)
                <x-ui.card @class(['bg-brand-50/35 dark:bg-brand-950/15' => $loop->first, 'bg-white dark:bg-slate-900' => ! $loop->first])>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($value) }}</p>
                </x-ui.card>
            @endforeach
        </section>
    @endif

    @if ($pipeline)
        <x-ui.card title="Avance del flujo operativo" description="Distribución actual de los lotes de tu empresa.">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($pipeline as $stage)
                    <div>
                        <div class="flex justify-between gap-2 text-sm">
                            <span class="text-slate-600 dark:text-slate-300">{{ $stage['label'] }}</span>
                            <strong class="text-slate-900 dark:text-slate-100">{{ number_format($stage['value']) }}</strong>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ round($stage['value'] / max(1, array_sum(array_column($pipeline, 'value'))) * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @endif

    @if ($metrics)
        <section aria-label="Indicadores de la empresa" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <x-ui.card @class(['bg-brand-50/35 dark:bg-brand-950/15' => $loop->index % 3 === 0, 'bg-white dark:bg-slate-900' => $loop->index % 3 !== 0])>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($metric['value']) }}</p>
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
                        <a wire:navigate href="{{ route($user->rol?->codigo === 'SUPERVISOR' && $user->hasPermission('calidad.ver') ? 'calidad.show' : 'inspecciones.show', $inspection->id_inspeccion) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-brand-300 hover:bg-brand-50/50 dark:border-slate-700 dark:hover:border-brand-800 dark:hover:bg-brand-900/25">
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
                        <a wire:navigate href="{{ route('lotes.show', $lot->id_lote) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-brand-300 hover:bg-brand-50/50 dark:border-slate-700 dark:hover:border-brand-800 dark:hover:bg-brand-900/25">
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
