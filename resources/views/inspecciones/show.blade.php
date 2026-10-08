<x-layouts.app title="Detalle de inspección">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <x-ui.back-link :href="route('inspecciones.index')" label="Volver a inspecciones" />
                <div class="mt-3 flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                        <x-ui.icon name="camera" class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Inspección #{{ $inspeccion->id_inspeccion }}</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lote {{ $inspeccion->muestreo?->lote?->codigo_lote }} · {{ str_replace('_',' ',$inspeccion->estado) }}</p>
                    </div>
                </div>
            </div>
        </div>
        @include('inspecciones._mensajes')
        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.card>
                <div class="flex items-start justify-between gap-3"><div><p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Muestra</p><p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $inspeccion->muestreo->tamano_muestra }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Frutos seleccionados</p></div><span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300"><x-ui.icon name="clipboard-list" class="h-4 w-4" /></span></div>
            </x-ui.card>
            <x-ui.card>
                <div class="flex items-start justify-between gap-3"><div><p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Evidencias</p><p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $inspeccion->evidencias->count() }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Fotografías registradas</p></div><span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300"><x-ui.icon name="camera" class="h-4 w-4" /></span></div>
            </x-ui.card>
            <x-ui.card>
                <div class="flex items-start justify-between gap-3"><div><p class="text-sm font-semibold text-slate-600 dark:text-slate-300">Hallazgos manuales</p><p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $inspeccion->hallazgos->count() }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Hallazgos adicionales</p></div><span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300"><x-ui.icon name="file-text" class="h-4 w-4" /></span></div>
            </x-ui.card>
        </div>
        <x-ui.card title="Flujo de trabajo" description="Captura, analiza y valida los resultados de tu inspección.">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <a class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700" href="{{ route('inspecciones.evidencias', $inspeccion->id_inspeccion) }}"><x-ui.icon name="camera" class="h-4 w-4" />Captura y análisis IA</a>
                <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700" href="{{ route('inspecciones.validacion', $inspeccion->id_inspeccion) }}"><x-ui.icon name="check" class="h-4 w-4" />Validar resultados</a>
                @if(auth()->user()->hasPermission('calidad.ver') && $inspeccion->estado === 'VALIDADA')
                    <a class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-300 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-900/20" href="{{ route('calidad.show', $inspeccion->id_inspeccion) }}"><x-ui.icon name="file-text" class="h-4 w-4" />Ver dictamen</a>
                @endif
            </div>
        </x-ui.card>
        <x-ui.card title="Información de inspección">
            <div class="grid gap-4 text-sm text-slate-700 dark:text-slate-200 sm:grid-cols-2">
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Inspector:</span><br>{{ $inspeccion->inspector?->nombre_completo ?? 'Pendiente de asignación' }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Muestreo:</span><br><a class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline dark:text-brand-300" href="{{ route('muestreos.show', $inspeccion->id_muestreo) }}">#{{ $inspeccion->id_muestreo }} <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Validada en:</span><br>{{ $inspeccion->validada_en?->format('d/m/Y H:i') ?? 'Pendiente' }}</p>
                <p class="sm:col-span-2"><span class="font-semibold text-slate-900 dark:text-slate-100">Observaciones:</span><br>{{ $inspeccion->observaciones ?: 'Sin observaciones' }}</p>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
