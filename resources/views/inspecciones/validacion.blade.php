<x-layouts.app title="Validar resultados IA">
    @php
        $editable = $inspeccion->estado === 'EN_CAPTURA'
            && auth()->user()->rol?->codigo === 'INSPECTOR'
            && auth()->user()->hasPermission('inspecciones.validar')
            && (!$inspeccion->id_inspector || (int) $inspeccion->id_inspector === (int) auth()->id());
        $deteccionesTotales = $inspeccion->evidencias->sum(fn($foto) => count($foto->resultado_ia['detections'] ?? []));
        $validadasTotales = $inspeccion->evidencias->sum(fn($foto) => $foto->validaciones->count());
    @endphp
    <div class="space-y-5" data-validation-workspace>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <x-ui.back-link :href="route('inspecciones.evidencias', $inspeccion->id_inspeccion)" label="Volver a evidencias" />
                <h1 class="mt-3 text-xl font-semibold text-slate-900 dark:text-slate-100 sm:text-2xl">Revisar fotografías</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inspección #{{ $inspeccion->id_inspeccion }} · Revisa la imagen, sus detecciones y tu conclusión antes de enviar al supervisor.</p>
            </div>
            <span class="rounded-xl bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">{{ $editable ? 'En revisión del inspector' : ($inspeccion->estado === 'VALIDADA' ? 'Expediente cerrado / solo lectura' : 'Solo consulta · corresponde al inspector asignado') }}</span>
        </div>


        <div data-review-navigation class="hidden flex-wrap items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-700"><button type="button" data-review-prev aria-label="Fotografía anterior" class="rounded-lg p-2 text-brand-700 dark:text-brand-300"><x-ui.icon name="arrow-left" class="h-5 w-5" /></button><label class="min-w-0 flex-1 text-sm dark:text-slate-200">Fotografía<select data-review-select class="mt-2 w-full min-w-0 rounded-lg border border-slate-300 bg-white p-2 dark:border-slate-600 dark:bg-slate-900">@foreach($inspeccion->evidencias as $foto)<option value="{{ $foto->id_evidencia }}">Evidencia #{{ $foto->id_evidencia }} · {{ isset($revisiones[$foto->id_evidencia]) ? 'Revisada' : 'Pendiente' }}</option>@endforeach</select></label><button type="button" data-review-next aria-label="Fotografía siguiente" class="rounded-lg p-2 text-brand-700 dark:text-brand-300"><x-ui.icon name="chevron-right" class="h-5 w-5" /></button><span data-review-position class="text-xs text-slate-500" aria-live="polite"></span></div>
        @include('inspecciones._mensajes')
        <div data-validation-message class="hidden rounded-xl border px-4 py-3 text-sm" role="status" aria-live="polite"></div>
        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,360px)]">
            <section class="min-w-0 space-y-4" aria-label="Predicciones de IA">
                <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Fotografías registradas</p><p class="mt-1 text-xl font-semibold dark:text-slate-100">{{ $inspeccion->evidencias->count() }}</p></div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Detecciones encontradas</p><p class="mt-1 text-xl font-semibold dark:text-slate-100">{{ $deteccionesTotales }}</p></div>
                    <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Detecciones revisadas</p><p class="mt-1 text-xl font-semibold dark:text-slate-100" data-validation-count>{{ $validadasTotales }}</p></div><div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500">Fotografías revisadas</p><p class="mt-1 text-xl font-semibold dark:text-slate-100"><span data-human-reviewed>{{ $progreso['revisadas'] }}</span> / {{ $progreso['fotos'] }}</p></div>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">El modelo actual v1 detecta orugas y saltamontes. Para este lote de {{ $inspeccion->muestreo->lote->producto }}, no determina por sí solo la calidad comercial ni garantiza ausencia de problemas. La revisión de la fotografía corresponde al inspector.</div>
                @forelse($inspeccion->evidencias as $foto)
                    @php $conclusionPropuesta = $foto->validaciones->where('confirmada', true)->isNotEmpty() || $inspeccion->hallazgos->where('id_evidencia', $foto->id_evidencia)->isNotEmpty() ? 'CON_HALLAZGOS' : 'SIN_HALLAZGOS'; @endphp
                    <article data-review-photo="{{ $foto->id_evidencia }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3 dark:border-slate-700 sm:px-5"><p class="font-semibold text-slate-900 dark:text-slate-100">Fotografía #{{ $foto->id_evidencia }} · {{ $foto->fruto?->codigo ?? 'Registro histórico' }}</p><x-ui.state-badge :state="$foto->estado_ia" :label="$foto->estado_ia === 'COMPLETADO' ? 'Analizada' : null" /></div>
                        <div class="grid gap-4 p-4 lg:grid-cols-2 sm:p-5">
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-1">
                                @foreach(['Original' => $foto->imagen_url, 'Resultado IA' => $foto->imagen_detectada_url] as $label => $url)
                                    @if($url)<div class="rounded-xl bg-slate-100 p-2 dark:bg-slate-800"><a data-image-zoom data-image-group="{{ $foto->id_evidencia }}" data-image-kind="{{ $label === 'Original' ? 'original' : 'resultado' }}" href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="Ampliar {{ $label }} de evidencia {{ $foto->id_evidencia }}"><img class="max-h-80 w-full object-contain" src="{{ $url }}" alt="{{ $label }} de evidencia {{ $foto->id_evidencia }}" loading="lazy"></a><p class="mt-2 text-center text-xs text-slate-500">{{ $label }} · pulsa para ampliar</p></div>@endif
                                @endforeach
                            </div>
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
                        <div data-image-review="{{ $foto->id_evidencia }}" data-review-current="{{ isset($revisiones[$foto->id_evidencia]) ? '1' : '0' }}" class="border-t border-slate-200 p-4 dark:border-slate-700 sm:p-5">
                            <p data-review-changed class="mb-3 hidden text-sm font-semibold text-amber-700 dark:text-amber-300">Cambió la evaluación: guarda nuevamente la revisión de esta imagen.</p>
                            @if(isset($revisiones[$foto->id_evidencia]))
                                <p class="mb-3 text-sm font-medium text-emerald-700 dark:text-emerald-300">Revisión guardada · {{ str_replace('_', ' ', $revisiones[$foto->id_evidencia]['conclusion']) }}</p>
                                <p class="mb-3 text-sm text-slate-500">{{ $revisiones[$foto->id_evidencia]['justificacion'] ?? '' }}</p>
                            @else
                                <p class="mb-3 text-sm text-amber-700 dark:text-amber-300">Pendiente de conclusión humana, aunque no haya detecciones IA.</p>
                            @endif
                            @if($editable)
                                <form data-ajax-form method="POST" action="{{ route('inspecciones.revision.store', [$inspeccion->id_inspeccion, $foto->id_evidencia]) }}" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="conclusion" value="{{ $conclusionPropuesta }}">
                                    <div class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800 dark:text-slate-200"><p class="font-semibold">Conclusión propuesta según los registros</p><p data-proposed-conclusion class="mt-1">{{ $conclusionPropuesta === 'CON_HALLAZGOS' ? 'Con hallazgos confirmados o registrados manualmente.' : 'Sin hallazgos registrados. Revisa personalmente la imagen antes de confirmarlo.' }}</p></div>
                                    <label class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300"><input type="checkbox" name="imagen_pertinente" value="1" required class="mt-1">Confirmo que la imagen muestra el fruto identificado de esta muestra y permite inspeccionarlo.</label>
                                    <label class="block text-sm font-medium dark:text-slate-100">Comentario {{ $foto->estado_ia !== 'COMPLETADO' ? '(obligatorio: justifica la revisión sin IA)' : '(opcional)' }}
                                        <textarea name="justificacion" rows="2" maxlength="2000" @required($foto->estado_ia !== 'COMPLETADO') class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm dark:border-slate-600 dark:bg-slate-900">{{ $revisiones[$foto->id_evidencia]['justificacion'] ?? '' }}</textarea>
                                    </label>
                                    <label class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300"><input type="checkbox" name="revision_completa" value="1" required class="mt-1">Revisé toda la fotografía y registré los hallazgos que la IA omitió.</label>
                                    <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Guardar revisión de esta fotografía</button><p class="text-xs text-slate-500">Guarda tu evaluación de esta imagen. La inspección seguirá abierta hasta enviarla al supervisor.</p>
                                </form>
                                <details class="mt-4 rounded-xl border border-amber-200 p-3 text-sm dark:border-amber-900"><summary class="cursor-pointer font-semibold text-amber-800 dark:text-amber-300">La fotografía no corresponde al fruto o no permite revisarlo</summary><p class="my-3 text-xs text-slate-500">Descártala para evaluación y toma una nueva. Se conservará su historial y no contará para completar la muestra.</p><form data-ajax-form method="POST" action="{{ route('inspecciones.evidencias.descartar', [$inspeccion->id_inspeccion, $foto->id_evidencia]) }}" class="space-y-3">@csrf<label class="block">Motivo del descarte *<textarea name="motivo_descarte" required maxlength="1500" rows="2" class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 dark:border-slate-600 dark:bg-slate-900"></textarea></label><x-ui.button type="submit" variant="secondary">Descartar fotografía y reemplazarla</x-ui.button></form></details>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800">Primero agrega fotografías en Captura de evidencias.</div>
                @endforelse
            </section>
            <aside class="min-w-0 space-y-4 xl:sticky xl:top-24">
                <x-ui.card title="Problemas adicionales" description="Registra aquí solo los problemas que observaste y que no aparecen entre las detecciones de IA.">
                    <div data-hallazgos-list class="space-y-2">
                        @foreach($inspeccion->hallazgos as $hallazgo)
                            <p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-200"><span class="text-xs font-medium">{{ str_replace('_', ' ', $naturalezas[$hallazgo->id_hallazgo] ?? 'Hallazgo histórico') }} · Evidencia #{{ $hallazgo->id_evidencia ?? 'sin asociación' }}</span><br><strong>{{ $hallazgo->clase }}</strong> — {{ $hallazgo->observaciones }}</p>
                        @endforeach
                    </div>
                    <p data-hallazgos-empty @class(['text-sm text-slate-500 dark:text-slate-400', 'hidden' => $inspeccion->hallazgos->isNotEmpty()])>Aún no se han registrado hallazgos manuales.</p>
                    @if($editable)
                        <details data-manual-findings class="mt-4"><summary class="cursor-pointer text-sm font-semibold text-brand-700 dark:text-brand-300">Sí, encontré un problema adicional</summary><p class="mt-2 text-xs text-slate-500">Si no encontraste otros problemas, continúa con la revisión de la fotografía; no necesitas completar este formulario.</p>
                        <form data-hallazgo-form method="POST" action="{{ route('inspecciones.hallazgos.store', $inspeccion->id_inspeccion) }}" class="mt-4 space-y-3">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Tipo de problema *<select name="naturaleza" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"><option value="">Selecciona la naturaleza</option><option value="ORGANISMO">Organismo / plaga visible</option><option value="ENFERMEDAD">Enfermedad</option><option value="DANO_PLAGA">Daño atribuible a plaga</option><option value="DEFECTO_FISICO">Defecto físico</option><option value="OTRO">Otro hallazgo</option></select></label>
                            <div><label for="clase" class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-200">Problema observado *</label><x-ui.master-select name="clase" kind="defecto" required /></div>
                            <div><label for="id_evidencia" class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-200">Fotografía asociada *</label><select required id="id_evidencia" name="id_evidencia" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"><option value="">Selecciona la fotografía</option>@foreach($inspeccion->evidencias as $foto)<option value="{{ $foto->id_evidencia }}">Evidencia #{{ $foto->id_evidencia }}</option>@endforeach</select></div>
                            <div><label for="observaciones-manuales" class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-200">¿Qué observaste? *</label><textarea id="observaciones-manuales" name="observaciones" rows="3" required maxlength="2000" placeholder="Describe lo observado" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></textarea></div>
                            <button type="submit" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-600 dark:text-slate-200"><span class="inline-flex items-center gap-2"><x-ui.icon name="plus" class="h-4 w-4" />Agregar hallazgo</span></button>
                        </form></details>
                    @endif
                </x-ui.card>
                @if($editable)
                    <div id="cerrar-inspeccion"></div><x-ui.card title="Finalizar y enviar al supervisor" description="Finaliza la validación y envía el expediente al supervisor; aquí no se aprueba ni rechaza el lote.">
                        <form data-ajax-form method="POST" action="{{ route('inspecciones.finalizar', $inspeccion->id_inspeccion) }}" class="space-y-3">
                            @csrf
                            <div class="rounded-xl bg-slate-50 p-3 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300"><p>Cobertura: {{ $progreso['cubiertos'] }} / {{ $progreso['frutos'] }} frutos.</p><p>Revisión vigente: <span data-review-check-count>{{ $progreso['revisadas'] }}</span> / {{ $progreso['fotos'] }} fotografías.</p><p class="mt-2">{{ $progreso['lista'] ? 'Expediente listo para envío.' : 'Faltan '.($progreso['fotos'] - $progreso['revisadas']).' fotografías por revisar y '.$progreso['faltantes'].' frutos por fotografiar. Completa estas tareas antes de enviar.' }}</p></div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Guarda una revisión por fotografía. Si modificas detecciones o hallazgos, confirma otra vez la revisión de esa imagen.</p>
                            <label for="obs-final" class="block text-xs font-semibold text-slate-700 dark:text-slate-200">Observaciones finales</label>
                            <textarea id="obs-final" name="observaciones" maxlength="3000" rows="3" placeholder="Resumen técnico de la inspección" class="w-full rounded-lg border border-slate-300 bg-white p-3 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></textarea>
                            <p class="text-xs text-slate-500">Enviar finaliza tu etapa y bloquea las modificaciones ordinarias. El supervisor emitirá la decisión de calidad.</p>
                            <button data-review-finalize data-review-covered="{{ $progreso['faltantes'] === 0 ? '1' : '0' }}" data-review-total="{{ $progreso['fotos'] }}" @disabled(!$progreso['lista']) type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-2.5 disabled:cursor-not-allowed disabled:opacity-50 text-sm font-semibold text-white hover:bg-brand-700">Enviar inspección al supervisor</button>
                        </form>
                    </x-ui.card>
                @else
                    <x-ui.card :title="$inspeccion->estado === 'VALIDADA' ? 'Inspección cerrada' : 'Revisión en solo consulta'" description="La revisión corresponde al inspector asignado; la decisión final pertenece al supervisor."><a wire:navigate class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 dark:text-brand-300" href="{{ route('inspecciones.show', $inspeccion->id_inspeccion) }}">Ver resumen de inspección <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></x-ui.card>
                @endif
            </aside>
        </div>
    </div>
    <script src="{{ asset('js/inspection-validation.js') }}?v=20261025-human-review" defer></script>
    <x-ui.image-viewer />
</x-layouts.app>
