<x-layouts.app title="Validar resultados IA">
    @php
        $editable = $inspeccion->estado === 'EN_CAPTURA'
            && auth()->user()->rol?->codigo === 'INSPECTOR'
            && auth()->user()->hasPermission('inspecciones.validar');
        $deteccionesTotales = $inspeccion->evidencias->sum(fn($foto) => count($foto->resultado_ia['detections'] ?? []));
        $validadasTotales = $inspeccion->evidencias->sum(fn($foto) => $foto->validaciones->count());
    @endphp
    <div class="space-y-5" data-validation-workspace>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <x-ui.back-link :href="route('inspecciones.evidencias', $inspeccion->id_inspeccion)" label="Volver a evidencias" />
                <h1 class="mt-3 text-xl font-semibold text-slate-900 dark:text-slate-100 sm:text-2xl">Validación de detecciones</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inspección #{{ $inspeccion->id_inspeccion }} · Confirma o descarta cada predicción antes de cerrar.</p>
            </div>
            <span class="rounded-xl bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">{{ $editable ? 'En revisión del inspector' : 'Expediente cerrado / solo lectura' }}</span>
        </div>
        @include('inspecciones._mensajes')
        <div data-validation-message class="hidden rounded-xl border px-4 py-3 text-sm" role="status" aria-live="polite"></div>
        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,360px)]">
            <section class="min-w-0 space-y-4" aria-label="Predicciones de IA">
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Fotografías</p><p class="mt-1 text-xl font-semibold dark:text-slate-100">{{ $inspeccion->evidencias->count() }}</p></div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Detecciones IA</p><p class="mt-1 text-xl font-semibold dark:text-slate-100">{{ $deteccionesTotales }}</p></div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Validadas</p><p class="mt-1 text-xl font-semibold dark:text-slate-100" data-validation-count>{{ $validadasTotales }}</p></div>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">El modelo v1 detecta orugas/saltamontes, pero no está validado específicamente para palta. Una predicción de IA no determina la calidad comercial del lote.</div>
                @forelse($inspeccion->evidencias as $foto)
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3 dark:border-slate-700 sm:px-5"><p class="font-semibold text-slate-900 dark:text-slate-100">Evidencia #{{ $foto->id_evidencia }}</p><x-ui.state-badge :state="$foto->estado_ia" :label="$foto->estado_ia === 'COMPLETADO' ? 'Analizada' : null" /></div>
                        <div class="grid gap-4 p-4 sm:grid-cols-[minmax(0,210px)_minmax(0,1fr)] sm:p-5">
                            <div class="flex items-center justify-center rounded-xl bg-slate-100 p-2 dark:bg-slate-800"><img class="max-h-64 w-full object-contain" src="{{ $foto->imagen_detectada_url ?: $foto->imagen_url }}" alt="Resultado visual de evidencia {{ $foto->id_evidencia }}" loading="lazy"></div>
                            <div class="min-w-0 space-y-3">
                                @if($foto->estado_ia !== 'COMPLETADO')
                                    <p class="text-sm text-amber-700 dark:text-amber-300">Esta fotografía no tiene resultado IA completo. Puedes registrar un hallazgo manual si el microservicio no está disponible.</p>
                                @elseif(count($foto->resultado_ia['detections'] ?? []) === 0)
                                    <p class="text-sm text-slate-600 dark:text-slate-300">Sin detecciones por encima del umbral. Eso no garantiza ausencia de defectos.</p>
                                @else
                                    @foreach(($foto->resultado_ia['detections'] ?? []) as $indice => $deteccion)
                                        @php($validacion = $foto->validaciones->firstWhere('indice_deteccion', $indice))
                                        <div data-validation-row data-saved="{{ $validacion ? '1' : '0' }}" class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
                                            <div class="flex flex-wrap items-center justify-between gap-2"><p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $deteccion['class'] ?? 'Clase no identificada' }}</p><span class="text-xs text-slate-500 dark:text-slate-400">Confianza {{ number_format((float) ($deteccion['confidence'] ?? 0) * 100, 1) }} %</span></div>
                                            <p data-validation-state class="mt-1 text-xs font-medium text-brand-700 dark:text-brand-300">{{ $validacion ? ($validacion->confirmada ? 'Confirmada y guardada' : 'Descartada y guardada') : 'Pendiente de revisión' }}</p>
                                            @if($editable)
                                                <form data-validation-form class="mt-3 grid gap-2 md:grid-cols-[minmax(0,130px)_minmax(0,1fr)_auto]" method="POST" action="{{ route('inspecciones.validacion.detectar', [$inspeccion->id_inspeccion, $foto->id_evidencia]) }}">
                                                    @csrf
                                                    <input type="hidden" name="indice_deteccion" value="{{ $indice }}">
                                                    <label class="sr-only" for="confirmada-{{ $foto->id_evidencia }}-{{ $indice }}">Evaluación</label>
                                                    <select id="confirmada-{{ $foto->id_evidencia }}-{{ $indice }}" name="confirmada" required class="min-w-0 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                                        <option value="1" @selected($validacion?->confirmada === true)>Confirmar</option><option value="0" @selected($validacion?->confirmada === false)>Descartar</option>
                                                    </select>
                                                    <input name="observaciones" maxlength="2000" value="{{ $validacion?->observaciones }}" placeholder="Observación (opcional)" class="min-w-0 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                                    <button type="submit" class="rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50">Guardar</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800">Primero agrega fotografías en Captura de evidencias.</div>
                @endforelse
            </section>
            <aside class="min-w-0 space-y-4 xl:sticky xl:top-5">
                <x-ui.card title="Hallazgos manuales" description="Añade problemas que la IA omitió o documenta una evaluación sin IA.">
                    <div data-hallazgos-list class="space-y-2">
                        @foreach($inspeccion->hallazgos as $hallazgo)
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-200"><strong>{{ $hallazgo->clase }}</strong> — {{ $hallazgo->observaciones }}</p>
                        @endforeach
                    </div>
                    <p data-hallazgos-empty @class(['text-sm text-slate-500 dark:text-slate-400', 'hidden' => $inspeccion->hallazgos->isNotEmpty()])>Aún no se han registrado hallazgos manuales.</p>
                    @if($editable)
                        <form data-hallazgo-form method="POST" action="{{ route('inspecciones.hallazgos.store', $inspeccion->id_inspeccion) }}" class="mt-4 space-y-3">
                            @csrf
                            <div><label for="clase" class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-200">Clase o hallazgo *</label><x-ui.master-select name="clase" kind="defecto" required /></div>
                            <div><label for="id_evidencia" class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-200">Fotografía asociada</label><select id="id_evidencia" name="id_evidencia" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"><option value="">Hallazgo general</option>@foreach($inspeccion->evidencias as $foto)<option value="{{ $foto->id_evidencia }}">Evidencia #{{ $foto->id_evidencia }}</option>@endforeach</select></div>
                            <div><label for="observaciones-manuales" class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-200">Justificación *</label><textarea id="observaciones-manuales" name="observaciones" rows="3" required maxlength="2000" placeholder="Describe lo observado" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></textarea></div>
                            <button type="submit" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:text-slate-200">+ Agregar hallazgo</button>
                        </form>
                    @endif
                </x-ui.card>
                @if($editable)
                    <x-ui.card title="Cerrar inspección" description="Finaliza la validación y envía el expediente al supervisor; aquí no se aprueba ni rechaza el lote.">
                        <form data-ajax-form method="POST" action="{{ route('inspecciones.finalizar', $inspeccion->id_inspeccion) }}" class="space-y-3">
                            @csrf
                            <p class="text-xs text-slate-500 dark:text-slate-400">Revisa todas las detecciones y los análisis pendientes antes de continuar.</p>
                            <label for="obs-final" class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Observaciones finales</label>
                            <textarea id="obs-final" name="observaciones" maxlength="3000" rows="3" placeholder="Resumen técnico de la inspección" class="w-full rounded-lg border border-slate-300 bg-white p-3 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></textarea>
                            <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Validar y enviar al supervisor</button>
                        </form>
                    </x-ui.card>
                @else
                    <x-ui.card title="Inspección cerrada" description="Este expediente está validado. La decisión final pertenece al supervisor."><a wire:navigate class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 dark:text-brand-300" href="{{ route('inspecciones.show', $inspeccion->id_inspeccion) }}">Ver resumen de inspección <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></x-ui.card>
                @endif
            </aside>
        </div>
    </div>
    <script src="{{ asset('js/inspection-validation.js') }}" defer></script>
</x-layouts.app>
