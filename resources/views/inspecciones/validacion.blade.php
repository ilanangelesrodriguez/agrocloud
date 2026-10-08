<x-layouts.app title="Validación de resultados">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <x-ui.back-link :href="route('inspecciones.evidencias', $inspeccion->id_inspeccion)" label="Volver a fotografías" />
                <div class="mt-3 flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300"><x-ui.icon name="check" class="h-5 w-5" /></div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Validación de resultados</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Confirma o descarta cada predicción. Puedes añadir hallazgos no detectados por el modelo.</p>
                    </div>
                </div>
            </div>
        </div>
        @include('inspecciones._mensajes')
        <x-ui.card title="Predicciones por evidencia" description="Detecciones provisionales: el modelo v1 no ha sido validado específicamente para palta.">
            <div class="space-y-5">
                @forelse($inspeccion->evidencias as $foto)
                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3"><div class="flex items-center gap-2"><span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-ui.icon name="camera" class="h-4 w-4" /></span><p class="font-semibold text-slate-900 dark:text-slate-100">Evidencia #{{ $foto->id_evidencia }}</p></div><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $foto->estado_ia }}</span></div>
                        <div class="grid gap-5 lg:grid-cols-[180px_1fr]">
                            <img class="h-44 w-full rounded-xl bg-slate-100 object-contain dark:bg-slate-800" src="{{ $foto->imagen_detectada_url ?: $foto->imagen_url }}" alt="Imagen evidencia {{ $foto->id_evidencia }}" loading="lazy">
                            <div class="space-y-3">
                                @if($foto->estado_ia !== 'COMPLETADO')
                                    <p class="text-sm text-amber-700 dark:text-amber-300">Análisis pendiente o con error. Puedes registrar hallazgos manuales si la IA no está disponible.</p>
                                @elseif(count($foto->resultado_ia['detections'] ?? []) === 0)
                                    <p class="text-sm text-slate-500 dark:text-slate-400">YOLO no encontró detecciones sobre el umbral. Esto no demuestra ausencia de plagas.</p>
                                @else
                                    @foreach(($foto->resultado_ia['detections'] ?? []) as $i => $deteccion)
                                        @php($validacion = $foto->validaciones->firstWhere('indice_deteccion', $i))
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/70">
                                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $deteccion['class'] ?? 'Clase' }} · {{ number_format((float) ($deteccion['confidence'] ?? 0) * 100, 1) }}%</p>
                                            @if($validacion)<p class="mt-1 text-xs text-brand-700 dark:text-brand-300">{{ $validacion->confirmada ? 'Confirmada' : 'Descartada' }} · Guardada</p>@endif
                                            @if($inspeccion->estado === 'EN_CAPTURA' && auth()->user()->hasPermission('inspecciones.validar'))
                                                <form class="mt-3 grid gap-2 sm:grid-cols-[auto_1fr_auto]" method="POST" action="{{ route('inspecciones.validacion.detectar', [$inspeccion->id_inspeccion, $foto->id_evidencia]) }}">@csrf
                                                    <input type="hidden" name="indice_deteccion" value="{{ $i }}">
                                                    <label class="sr-only" for="confirmada-{{ $foto->id_evidencia }}-{{ $i }}">Evaluación</label>
                                                    <select id="confirmada-{{ $foto->id_evidencia }}-{{ $i }}" name="confirmada" required class="rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-xs dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                                        <option value="1" @selected($validacion?->confirmada === true)>Confirmar</option><option value="0" @selected($validacion?->confirmada === false)>Descartar</option>
                                                    </select>
                                                    <input name="observaciones" maxlength="2000" value="{{ $validacion?->observaciones }}" placeholder="Observación opcional" class="min-w-24 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                                    <x-ui.button type="submit" class="rounded-lg px-3 py-2 text-xs"><span class="inline-flex items-center gap-1.5"><x-ui.icon name="save" class="h-3.5 w-3.5" />Guardar</span></x-ui.button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 px-5 py-8 text-center dark:border-slate-700"><div class="mx-auto grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"><x-ui.icon name="camera" class="h-5 w-5" /></div><p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Primero sube al menos una fotografía.</p></div>
                @endforelse
            </div>
        </x-ui.card>
        <x-ui.card title="Hallazgos manuales" description="Registra plagas u observaciones que la IA no detectó; también funciona si el servicio IA está temporalmente indisponible.">
            <div class="space-y-2">
                @forelse($inspeccion->hallazgos as $h)
                    <p class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"><strong>{{ $h->clase }}:</strong> {{ $h->observaciones }}</p>
                @empty<p class="text-sm text-slate-500">Aún no hay hallazgos manuales.</p>@endforelse
            </div>
            @if($inspeccion->estado === 'EN_CAPTURA' && auth()->user()->hasPermission('inspecciones.validar'))
                <form method="POST" action="{{ route('inspecciones.hallazgos.store', $inspeccion->id_inspeccion) }}" class="mt-5 grid gap-4 md:grid-cols-2">@csrf
                    <div><label for="clase" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Clase o hallazgo *</label><input id="clase" name="clase" required maxlength="150" placeholder="Ej. Hallazgo visual" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></div>
                    <div><label for="id_evidencia" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Evidencia relacionada</label><select id="id_evidencia" name="id_evidencia" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"><option value="">General</option>@foreach($inspeccion->evidencias as $foto)<option value="{{ $foto->id_evidencia }}">Fotografía #{{ $foto->id_evidencia }}</option>@endforeach</select></div>
                    <div class="md:col-span-2"><label for="observaciones-manuales" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Justificación *</label><textarea id="observaciones-manuales" name="observaciones" maxlength="2000" required rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"></textarea></div>
                    <div class="md:col-span-2"><x-ui.button type="submit" variant="secondary"><span class="inline-flex items-center gap-2"><x-ui.icon name="plus" class="h-4 w-4" />Agregar detección manual</span></x-ui.button></div>
                </form>
            @endif
        </x-ui.card>
        @if($inspeccion->estado === 'EN_CAPTURA' && auth()->user()->hasPermission('inspecciones.validar'))
            <x-ui.card title="Finalizar inspección" description="Después del cierre no se podrán modificar sus predicciones ni hallazgos. El supervisor recibirá el expediente.">
                <form method="POST" action="{{ route('inspecciones.finalizar', $inspeccion->id_inspeccion) }}" class="space-y-3">@csrf
                    <label for="obs-final" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Observaciones finales</label>
                    <textarea name="observaciones" id="obs-final" rows="3" maxlength="3000" class="w-full rounded-xl border border-slate-300 p-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" placeholder="Resumen de la inspección..."></textarea>
                    <div class="flex justify-end"><x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4" />Validar y enviar al supervisor</span></x-ui.button></div>
                </form>
            </x-ui.card>
        @else
            <x-ui.card title="Inspección validada"><p class="text-sm text-slate-600 dark:text-slate-300">Este expediente es de solo lectura. Revisa el dictamen del supervisor para continuar el proceso.</p></x-ui.card>
        @endif
    </div>
</x-layouts.app>
