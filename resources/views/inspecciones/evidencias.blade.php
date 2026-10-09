<x-layouts.app title="Captura y análisis IA">
    @php
        $puedeCapturar = $inspeccion->estado === 'EN_CAPTURA'
            && auth()->user()->rol?->codigo === 'INSPECTOR'
            && auth()->user()->hasPermission('inspecciones.capturar');
        $totalEvidencias = $inspeccion->evidencias->count();
        $analizadas = $inspeccion->evidencias->where('estado_ia', 'COMPLETADO')->count();
    @endphp
    <div class="space-y-5" data-evidence-workspace>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <x-ui.back-link :href="route('inspecciones.show', $inspeccion->id_inspeccion)" label="Volver a la inspección" />
                <h1 class="mt-3 text-xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 sm:text-2xl">Captura de evidencias y análisis IA</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inspección #{{ $inspeccion->id_inspeccion }} · Lote {{ $inspeccion->muestreo?->lote?->codigo_lote }} · {{ $inspeccion->muestreo?->tamano_muestra }} frutos seleccionados</p>
            </div>
            <a href="{{ route('inspecciones.validacion', $inspeccion->id_inspeccion) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                Ir a validación <x-ui.icon name="chevron-right" class="h-4 w-4" />
            </a>
        </div>
        @include('inspecciones._mensajes')
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500 dark:text-slate-400">Fotografías</p><p class="mt-1 text-xl font-semibold text-slate-900 dark:text-slate-100" data-evidence-count>{{ $totalEvidencias }}</p></div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500 dark:text-slate-400">Analizadas con IA</p><p class="mt-1 text-xl font-semibold text-slate-900 dark:text-slate-100" data-analyzed-count>{{ $analizadas }}</p></div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900"><p class="text-xs text-slate-500 dark:text-slate-400">Estado de inspección</p><p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $inspeccion->estado === 'EN_CAPTURA' ? 'En captura' : 'Validada / cerrada' }}</p></div>
        </div>
        <div data-workspace-message class="hidden rounded-xl border px-4 py-3 text-sm" role="status" aria-live="polite"></div>
        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,350px)_minmax(0,1fr)]">
            <div class="space-y-4 xl:sticky xl:top-5">
                @if($puedeCapturar)
                    <x-ui.card title="Añadir fotografía" description="Selecciona una imagen, revisa la vista previa y guárdala. Después podrás solicitar el análisis con IA.">
                        <form data-upload-form action="{{ route('inspecciones.evidencias.store', $inspeccion->id_inspeccion) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200" for="foto">Imagen del fruto</label>
                                <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" required data-file-input class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 file:mr-2 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-brand-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                                <p class="text-xs text-slate-500 dark:text-slate-400">JPG, PNG o WebP. Tamaño final máximo: 3 MiB. Optimización automática en navegadores compatibles.</p>
                            </div>
                            <div data-upload-preview class="hidden overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-800">
                                <img data-preview-image alt="Vista previa antes de subir" class="mx-auto max-h-44 w-full rounded-lg object-contain">
                                <p data-preview-meta class="mt-2 text-center text-xs text-slate-500 dark:text-slate-400"></p>
                            </div>
                            <button type="submit" data-upload-button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"><x-ui.icon name="camera" class="h-4 w-4" />Guardar fotografía</button>
                            <p data-upload-progress class="hidden text-center text-sm font-medium text-blue-700 dark:text-blue-300" role="status" aria-live="polite"></p>
                        </form>
                    </x-ui.card>
                @else
                    <x-ui.card title="Inspección en solo lectura" description="La captura está cerrada o tu rol no permite modificar este expediente.">
                        <p class="text-sm text-slate-600 dark:text-slate-300">Las evidencias guardadas se conservan para el dictamen del supervisor.</p>
                    </x-ui.card>
                @endif
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm dark:border-blue-900 dark:bg-blue-950/40">
                    <p class="font-semibold text-blue-900 dark:text-blue-200">Cómo funciona el análisis</p>
                    <ol class="mt-2 list-inside list-decimal space-y-1 text-blue-900/85 dark:text-blue-200/85">
                        <li>Guarda una fotografía.</li>
                        <li>Pulsa «Analizar con IA» en su tarjeta.</li>
                        <li>Revisa imagen marcada y nivel de confianza.</li>
                        <li>Confirma o descarta detecciones en Validación.</li>
                    </ol>
                    <p class="mt-3 text-xs text-blue-800 dark:text-blue-300">La IA utiliza el modelo YOLO v1 de orugas/saltamontes; sus predicciones no representan una clasificación comercial ni acreditan ausencia de plagas.</p>
                </div>
            </div>
            <section class="min-w-0 space-y-3" aria-label="Fotografías de la inspección">
                <div class="flex flex-wrap items-end justify-between gap-2"><div><h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Fotografías registradas</h2><p class="text-sm text-slate-500 dark:text-slate-400">Compara original y detección. Cada tarjeta se actualiza sin recargar la página.</p></div></div>
                <div data-evidence-list class="grid min-w-0 gap-4 2xl:grid-cols-2">
                    @foreach($inspeccion->evidencias->sortByDesc('id_evidencia') as $foto)
                        @include('inspecciones._evidencia', ['foto' => $foto])
                    @endforeach
                </div>
                <div data-evidence-empty @class(['rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-400', 'hidden' => $totalEvidencias > 0])>Todavía no hay fotografías. Agrega una en el panel de la izquierda para empezar.</div>
            </section>
        </div>
    </div>
    <script src="{{ asset('js/inspection-evidence.js') }}" defer></script>
</x-layouts.app>
