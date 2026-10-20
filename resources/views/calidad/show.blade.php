<x-layouts.app title="Dictamen de calidad">
    @php
        $detecciones = $inspeccion->evidencias->sum(fn($foto) => count($foto->resultado_ia['detections'] ?? []));
        $confirmadas = $inspeccion->evidencias->sum(fn($foto) => $foto->validaciones->where('confirmada', true)->count());
        $descartadas = $inspeccion->evidencias->sum(fn($foto) => $foto->validaciones->where('confirmada', false)->count());
    @endphp
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3"><div><x-ui.back-link :href="route('calidad.index')" label="Volver a pendientes" /><h1 class="mt-3 text-xl font-semibold text-slate-900 dark:text-slate-100 sm:text-2xl">Dictamen de calidad · Inspección #{{ $inspeccion->id_inspeccion }}</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lote {{ $inspeccion->muestreo?->lote?->codigo_lote }} · Revisión del supervisor</p></div><x-ui.state-badge :state="$inspeccion->dictamen?->decision ?? 'PENDIENTE'" :label="$inspeccion->dictamen?->decision ? null : 'Pendiente de decisión'" /></div>
        @include('inspecciones._mensajes')
        @if(auth()->user()->hasPermission('lotes.ver'))<a wire:navigate href="{{ route('incidencias.index', ['lote' => $inspeccion->muestreo->id_lote]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 dark:text-brand-300"><x-ui.icon name="clipboard-list" class="h-4 w-4" />Revisar incidencias del lote antes del dictamen</a>@endif
        <div class="grid gap-3 sm:grid-cols-4">
            @foreach([['Fotografías', $inspeccion->evidencias->count()], ['Detecciones IA', $detecciones], ['Confirmadas', $confirmadas], ['Descartadas', $descartadas]] as [$titulo, $valor])
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500 dark:text-slate-400">{{ $titulo }}</p><p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $valor }}</p></div>
            @endforeach
        </div>
        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,370px)]">
            <section class="min-w-0 space-y-4">
                <x-ui.card title="Evidencias y predicciones verificadas" description="La IA apoya la revisión. El supervisor decide según el protocolo de calidad y los hallazgos del inspector.">
                    <div class="grid gap-4 lg:grid-cols-2">
                        @forelse($inspeccion->evidencias as $foto)
                            <article class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                                <div class="flex h-52 items-center justify-center bg-slate-100 p-2 dark:bg-slate-800"><img src="{{ $foto->imagen_detectada_url ?: $foto->imagen_url }}" alt="Evidencia {{ $foto->id_evidencia }}" loading="lazy" class="max-h-full w-full object-contain"></div>
                                <div class="space-y-2 p-3"><div class="flex items-center justify-between gap-2"><p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Evidencia #{{ $foto->id_evidencia }}</p><x-ui.state-badge :state="$foto->estado_ia" /></div>
                                    <p class="text-xs text-slate-600 dark:text-slate-300">Revisión humana: {{ isset($revisiones[$foto->id_evidencia]) ? str_replace('_', ' ', $revisiones[$foto->id_evidencia]['conclusion']) : 'Pendiente / expediente anterior: solicitar reinspección' }}</p>
                                    <p class="text-xs text-slate-500">{{ $revisiones[$foto->id_evidencia]['justificacion'] ?? '' }}</p>
                                    @forelse(($foto->resultado_ia['detections'] ?? []) as $indice => $det)
                                        @php($v = $foto->validaciones->firstWhere('indice_deteccion', $indice))
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-2 text-xs dark:border-slate-700"><span class="text-slate-600 dark:text-slate-300">{{ $det['class'] ?? 'Clase' }} · {{ number_format((float) ($det['confidence'] ?? 0) * 100, 1) }}%</span><span @class(['font-semibold', 'text-emerald-700 dark:text-emerald-300' => $v?->confirmada === true, 'text-red-700 dark:text-red-300' => $v && !$v->confirmada, 'text-slate-500' => !$v])>{{ $v?->confirmada ? 'Confirmada' : ($v ? 'Descartada' : 'Sin validar') }}</span></div>
                                    @empty
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Sin detecciones registradas.</p>
                                    @endforelse
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-slate-500">No hay fotografías registradas.</p>
                        @endforelse
                    </div>
                </x-ui.card>
                <x-ui.card title="Observaciones del inspector y hallazgos manuales">
                    <p class="text-sm text-slate-700 dark:text-slate-200">{{ $inspeccion->observaciones ?: 'El inspector no registró observaciones finales.' }}</p>
                    <div class="mt-4 space-y-2">
                        @foreach($inspeccion->hallazgos as $hallazgo)
                            <p class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-200"><span class="text-xs font-medium">{{ str_replace('_', ' ', $naturalezas[$hallazgo->id_hallazgo] ?? 'Hallazgo histórico') }} · Evidencia #{{ $hallazgo->id_evidencia ?? 'sin asociación' }}</span><br><strong>{{ $hallazgo->clase }}:</strong> {{ $hallazgo->observaciones }}</p>
                        @endforeach
                    </div>
                </x-ui.card>
            </section>
            <aside class="min-w-0 space-y-4 xl:sticky xl:top-5">
                @if($inspeccion->dictamen)
                    <x-ui.card title="Dictamen registrado" description="La decisión ya fue registrada y no se puede duplicar para esta inspección.">
                        <dl class="space-y-4 text-sm"><div><dt class="text-xs text-slate-500">Decisión</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $inspeccion->dictamen->decision }}</dd></div><div><dt class="text-xs text-slate-500">Categoría</dt><dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $inspeccion->dictamen->categoria ?? 'No asignada' }}</dd></div><div><dt class="text-xs text-slate-500">Motivo</dt><dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $inspeccion->dictamen->motivo ?: 'Sin especificar' }}</dd></div><div><dt class="text-xs text-slate-500">Fundamento</dt><dd class="mt-1 whitespace-pre-line text-slate-800 dark:text-slate-200">{{ $inspeccion->dictamen->observaciones }}</dd></div></dl>
                        @if($inspeccion->dictamen->decision === 'OBSERVADO')<p class="mt-4 rounded-lg bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-950/40 dark:text-amber-200">Se creó una nueva inspección para repetir el análisis; las evidencias anteriores permanecen guardadas.</p>@endif
                    </x-ui.card>
                @elseif($inspeccion->estado === 'VALIDADA' && auth()->user()->rol?->codigo === 'SUPERVISOR' && auth()->user()->hasPermission('calidad.clasificar'))
                    <x-ui.card title="Emitir dictamen" description="La decisión libera, observa o rechaza el lote. La categoría comercial es una clasificación separada.">
                        <form data-ajax-form method="POST" action="{{ route('calidad.dictamen.store', $inspeccion->id_inspeccion) }}" class="space-y-4">
                            @csrf
                            <div><label for="decision" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Decisión *</label><select id="decision" name="decision" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"><option value="">Selecciona una decisión</option><option value="APROBADO" @selected(old('decision') === 'APROBADO')>Aprobar · liberar lote</option><option value="OBSERVADO" @selected(old('decision') === 'OBSERVADO')>Observar · solicitar reinspección</option><option value="RECHAZADO" @selected(old('decision') === 'RECHAZADO')>Rechazar lote</option></select></div>
                            <p class="rounded-lg bg-slate-50 p-3 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">Aprobado <x-ui.icon name="chevron-right" class="inline h-3.5 w-3.5 align-text-bottom" /> lote liberado. Observado <x-ui.icon name="chevron-right" class="inline h-3.5 w-3.5 align-text-bottom" /> nueva inspección. Rechazado <x-ui.icon name="chevron-right" class="inline h-3.5 w-3.5 align-text-bottom" /> lote rechazado.</p>
                            <div><label for="categoria" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Categoría comercial (opcional)</label><x-ui.master-select name="categoria" kind="categoria" /></div>
<x-ui.master-select name="motivo" kind="motivo" label="Motivo de rechazo u observación (opcional)" />
                            <div><label for="observaciones" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Fundamento de la decisión *</label><textarea id="observaciones" name="observaciones" required maxlength="3000" rows="5" placeholder="Detalla los criterios y resultados revisados" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">{{ old('observaciones') }}</textarea></div>
                            <label class="block text-sm font-semibold dark:text-slate-200">Protocolo de calidad y versión *<input name="protocolo_calidad" required maxlength="200" value="{{ old('protocolo_calidad') }}" placeholder="Ej. Control de ingreso, versión 2026-01" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm dark:border-slate-600 dark:bg-slate-900"></label>
                            <label class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300"><input type="checkbox" name="revision_independiente" value="1" required class="mt-1">Revisé personalmente las imágenes, las conclusiones del inspector y los criterios de aceptación.</label>
                            <p class="text-xs text-slate-500">Una categoría no representa una cantidad clasificada. Los pesos por categoría requieren medición física.</p>
                            <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Confirmar y guardar dictamen</button>
                        </form>
                    </x-ui.card>
                @else
                    <x-ui.card title="Pendiente de dictamen" description="Esta etapa corresponde al supervisor cuando el inspector ya validó la inspección."><p class="text-sm text-slate-600 dark:text-slate-300">No puedes registrar un dictamen en este estado o con este rol.</p></x-ui.card>
                @endif
                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-800 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-200">La confianza del modelo no equivale a porcentaje de calidad del lote. Revisa el protocolo interno antes de decidir.</div>
            </aside>
        </div>
    </div>
</x-layouts.app>
