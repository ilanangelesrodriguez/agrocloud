<x-layouts.app title="Detalle de muestreo">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <x-ui.back-link href="{{ route('muestreos.index') }}" label="Volver a muestreos" />
                <div class="mt-3 flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                        <x-ui.icon name="clipboard-list" class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Muestreo #{{ $muestreo->id_muestreo }}</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lote {{ $muestreo->lote?->codigo_lote }} · {{ $muestreo->tamano_muestra }} frutos · {{ $muestreo->metodo }}</p>
                    </div>
                </div>
            </div>
            @if(auth()->user()->hasPermission('inspecciones.crear') && $muestreo->estado !== 'COMPLETADO' && (!$muestreo->inspecciones->where('estado', 'VALIDADA')->count() || $muestreo->inspecciones->where('estado', 'EN_CAPTURA')->count()))
                <form action="{{ route('muestreos.inspecciones.store', $muestreo->id_muestreo) }}" method="POST">@csrf
                    <x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="camera" class="h-4 w-4" />Iniciar / continuar inspección</span></x-ui.button>
                </form>
            @endif
        </div>
        @include('inspecciones._mensajes')
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.card title="Datos del lote"><div class="space-y-3 text-sm text-slate-700 dark:text-slate-200">
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Código:</span> {{ $muestreo->lote?->codigo_lote }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Productor:</span> {{ $muestreo->lote?->productor?->nombre_razon_social ?? 'No registrado' }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Estado:</span> {{ $muestreo->lote?->estado?->label() }}</p>
            </div></x-ui.card>
            <x-ui.card title="Datos del muestreo"><div class="space-y-3 text-sm text-slate-700 dark:text-slate-200">
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Responsable:</span> {{ $muestreo->inspector?->nombre_completo }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Estado:</span> {{ str_replace('_',' ',$muestreo->estado) }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Observaciones:</span> {{ $muestreo->observaciones ?: 'Sin observaciones' }}</p>
            </div></x-ui.card>
        </div>
        <x-ui.card title="Inspecciones del muestreo" description="Las reinspecciones conservan los resultados anteriores.">
            <div class="space-y-3">
                @forelse($muestreo->inspecciones->sortByDesc('id_inspeccion') as $insp)
                    <a href="{{ route('inspecciones.show', $insp->id_inspeccion) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-4 hover:border-brand-300 dark:border-slate-700">
                        <div class="flex items-center gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-ui.icon name="camera" class="h-4 w-4" /></span><div><p class="font-semibold text-slate-900 dark:text-slate-100">Inspección #{{ $insp->id_inspeccion }}</p><p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $insp->inspector?->nombre_completo ?? 'Pendiente de asignación' }}</p></div></div>
                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 dark:text-brand-300">{{ str_replace('_',' ',$insp->estado) }} <x-ui.icon name="chevron-right" class="h-4 w-4" /></span>
                    </a>
                @empty
                    <p class="py-5 text-sm text-slate-500 dark:text-slate-400">No se han iniciado inspecciones.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
