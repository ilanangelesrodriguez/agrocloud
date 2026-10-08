<x-layouts.app title="Dictamen de calidad">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <x-ui.back-link :href="route('calidad.index')" label="Volver a dictámenes" />
                <div class="mt-3 flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"><x-ui.icon name="check" class="h-5 w-5" /></div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Dictamen de calidad · Inspección #{{ $inspeccion->id_inspeccion }}</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lote {{ $inspeccion->muestreo?->lote?->codigo_lote }} · Los resultados IA son orientativos y requieren criterio humano.</p>
                    </div>
                </div>
            </div>
        </div>
        @include('inspecciones._mensajes')
        <x-ui.card title="Resultados consolidados" description="Consulta las fotografías, las predicciones validadas y los hallazgos manuales antes del dictamen.">
            <div class="mb-5 flex flex-wrap items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300"><x-ui.icon name="camera" class="h-4 w-4" />{{ $inspeccion->evidencias->count() }} fotografías</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-ui.icon name="file-text" class="h-4 w-4" />{{ $inspeccion->hallazgos->count() }} hallazgos manuales</span>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                @foreach($inspeccion->evidencias as $foto)
                    <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                        <div class="bg-slate-100 p-3 dark:bg-slate-800"><img src="{{ $foto->imagen_detectada_url ?: $foto->imagen_url }}" loading="lazy" alt="Evidencia {{ $foto->id_evidencia }}" class="h-48 w-full rounded-xl object-contain"></div>
                        <div class="space-y-2 p-4">
                            <div class="flex items-center justify-between gap-2"><p class="font-semibold text-slate-900 dark:text-slate-100">Evidencia #{{ $foto->id_evidencia }}</p><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $foto->estado_ia }}</span></div>
                        @foreach(($foto->resultado_ia['detections'] ?? []) as $indice => $det)
                            @php($v = $foto->validaciones->firstWhere('indice_deteccion', $indice))
                            <p class="flex flex-wrap items-center gap-1 text-xs text-slate-600 dark:text-slate-300"><span class="font-semibold">{{ $det['class'] ?? 'Clase' }}</span><span>· {{ number_format((float)($det['confidence'] ?? 0) * 100, 1) }}%</span><span>· {{ $v?->confirmada ? 'Confirmada' : ($v ? 'Descartada' : 'Sin validar') }}</span></p>
                        @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @foreach($inspeccion->hallazgos as $h)
                <p class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"><strong>Manual: {{ $h->clase }}.</strong> {{ $h->observaciones }}</p>
            @endforeach
            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"><p class="font-semibold text-slate-900 dark:text-slate-100">Observaciones del inspector</p><p class="mt-1">{{ $inspeccion->observaciones ?? 'Sin observaciones' }}</p></div>
        </x-ui.card>
        @if($inspeccion->dictamen)
            <x-ui.card title="Dictamen registrado">
                <div class="grid gap-4 text-sm text-slate-700 dark:text-slate-200 sm:grid-cols-2"><p><span class="font-semibold text-slate-900 dark:text-slate-100">Decisión</span><br>{{ $inspeccion->dictamen->decision }}</p><p><span class="font-semibold text-slate-900 dark:text-slate-100">Categoría</span><br>{{ $inspeccion->dictamen->categoria ?? 'No asignada' }}</p><p class="sm:col-span-2"><span class="font-semibold text-slate-900 dark:text-slate-100">Observaciones</span><br>{{ $inspeccion->dictamen->observaciones }}</p></div>
            </x-ui.card>
        @elseif($inspeccion->estado === 'VALIDADA' && auth()->user()->hasPermission('calidad.clasificar'))
            <x-ui.card title="Registrar dictamen" description="Solo el supervisor toma la decisión. Si observa el lote se abrirá una reinspección, preservando el expediente anterior.">
                <form method="POST" action="{{ route('calidad.dictamen.store', $inspeccion->id_inspeccion) }}" class="space-y-4">@csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <div><label for="decision" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Decisión *</label><select id="decision" name="decision" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"><option value="">Selecciona...</option><option value="APROBADO">Aprobado</option><option value="OBSERVADO">Observado — solicitar reinspección</option><option value="RECHAZADO">Rechazado</option></select></div>
                        <div><label for="categoria" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Categoría (opcional)</label><input id="categoria" name="categoria" maxlength="100" placeholder="Según protocolo vigente" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></div>
                    </div>
                    <div><label for="observaciones" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Fundamento de decisión *</label><textarea id="observaciones" name="observaciones" required rows="4" maxlength="3000" placeholder="Explica el fundamento de la decisión..." class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></textarea></div>
                    <div class="flex justify-end"><x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="save" class="h-4 w-4" />Confirmar y guardar dictamen</span></x-ui.button></div>
                </form>
            </x-ui.card>
        @else
            <x-ui.card><p class="text-sm text-slate-600 dark:text-slate-300">El dictamen está reservado para una inspección validada y un supervisor autorizado.</p></x-ui.card>
        @endif
    </div>
</x-layouts.app>
