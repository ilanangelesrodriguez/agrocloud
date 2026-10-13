<x-layouts.app title="Detalle de inspección">
    @php
        $fotos = $inspeccion->evidencias->count();
        $analizadas = $inspeccion->evidencias->where('estado_ia', 'COMPLETADO')->count();
        $validada = $inspeccion->estado === 'VALIDADA';
    @endphp
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <x-ui.back-link :href="route('inspecciones.index')" label="Volver a inspecciones" />
                <h1 class="mt-3 text-xl font-semibold text-slate-900 dark:text-slate-100 sm:text-2xl">Inspección #{{ $inspeccion->id_inspeccion }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lote {{ $inspeccion->muestreo?->lote?->codigo_lote }} · Inspector: {{ $inspeccion->inspector?->nombre_completo ?? 'Pendiente de asignación' }}</p>
            </div>
            <x-ui.state-badge :state="$inspeccion->estado" :label="$validada ? 'Validada por inspector' : 'En captura y análisis'" />
        </div>
        @include('inspecciones._mensajes')
        <div class="grid gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Frutos de muestra</p><p class="mt-1 text-2xl font-semibold dark:text-slate-100">{{ $inspeccion->muestreo->tamano_muestra }}</p></div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Fotos</p><p class="mt-1 text-2xl font-semibold dark:text-slate-100">{{ $fotos }}</p></div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Analizadas IA</p><p class="mt-1 text-2xl font-semibold dark:text-slate-100">{{ $analizadas }}</p></div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Hallazgos manuales</p><p class="mt-1 text-2xl font-semibold dark:text-slate-100">{{ $inspeccion->hallazgos->count() }}</p></div>
        </div>
        <x-ui.card title="Etapas de control de calidad" description="El inspector valida la inspección; el supervisor emite el dictamen final del lote.">
            <div class="grid gap-3 md:grid-cols-3">
                <a wire:navigate href="{{ route('inspecciones.evidencias', $inspeccion->id_inspeccion) }}" class="group rounded-xl border border-slate-200 bg-slate-50 p-4 hover:border-brand-400 dark:border-slate-700 dark:bg-slate-800/50">
                    <div class="flex items-center justify-between"><span class="rounded-full bg-brand-100 px-2 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">01 · Captura</span><x-ui.icon name="camera" class="h-5 w-5 text-brand-600" /></div>
                    <p class="mt-3 font-semibold text-slate-900 dark:text-slate-100">Evidencias y análisis IA</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $analizadas }} de {{ $fotos }} fotografías analizadas. Sin recargas entre acciones.</p>
                    <p class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 dark:text-brand-300">Abrir captura <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /></p>
                </a>
                <a wire:navigate href="{{ route('inspecciones.validacion', $inspeccion->id_inspeccion) }}" class="group rounded-xl border border-slate-200 bg-slate-50 p-4 hover:border-brand-400 dark:border-slate-700 dark:bg-slate-800/50">
                    <div class="flex items-center justify-between"><span class="rounded-full bg-brand-100 px-2 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">02 · Validación</span><x-ui.icon name="check" class="h-5 w-5 text-brand-600" /></div>
                    <p class="mt-3 font-semibold text-slate-900 dark:text-slate-100">Revisar detecciones</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Confirmar, descartar o documentar hallazgos manuales.</p>
                    <p class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 dark:text-brand-300">{{ $validada ? 'Ver revisión' : 'Continuar revisión' }} <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /></p>
                </a>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <div class="flex items-center justify-between"><span class="rounded-full bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">03 · Dictamen</span><x-ui.icon name="shield" class="h-5 w-5 text-slate-600 dark:text-slate-300" /></div>
                    <p class="mt-3 font-semibold text-slate-900 dark:text-slate-100">Decisión del supervisor</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $inspeccion->dictamen ? 'Resultado: '.$inspeccion->dictamen->decision : ($validada ? 'Listo para revisión del supervisor.' : 'Disponible después de validar la inspección.') }}</p>
                    @if($validada && auth()->user()->hasPermission('calidad.ver'))
                        <a wire:navigate class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 dark:text-brand-300" href="{{ route('calidad.show', $inspeccion->id_inspeccion) }}">Ver dictamen <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /></a>
                    @endif
                </div>
            </div>
        </x-ui.card>
        <div class="grid gap-4 lg:grid-cols-2">
            <x-ui.card title="Datos vinculados">
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Lote</dt><dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $inspeccion->muestreo?->lote?->codigo_lote }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Muestreo</dt><dd><a wire:navigate class="inline-flex items-center gap-1 font-semibold text-brand-700 dark:text-brand-300" href="{{ route('muestreos.show', $inspeccion->id_muestreo) }}">#{{ $inspeccion->id_muestreo }} <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></dd></div>
                    <div><dt class="text-xs text-slate-500">Método</dt><dd class="text-slate-800 dark:text-slate-100">{{ $inspeccion->muestreo->metodo }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Validada en</dt><dd class="text-slate-800 dark:text-slate-100">{{ $inspeccion->validada_en?->format('d/m/Y H:i') ?: 'Aún sin validar' }}</dd></div>
                </dl>
            </x-ui.card>
            <x-ui.card title="Observaciones técnicas"><p class="text-sm text-slate-600 dark:text-slate-300">{{ $inspeccion->observaciones ?: 'No se han registrado observaciones finales.' }}</p></x-ui.card>
        </div>
    </div>
</x-layouts.app>
