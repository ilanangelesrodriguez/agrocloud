<x-layouts.app title="Fotografías y análisis IA">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <x-ui.back-link :href="route('inspecciones.show', $inspeccion->id_inspeccion)" label="Volver a la inspección" />
                <div class="mt-3 flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                        <x-ui.icon name="camera" class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Captura de evidencias y análisis IA</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lote {{ $inspeccion->muestreo?->lote?->codigo_lote }} · Modelo provisional v1 (saltamontes y orugas, no validado para palta)</p>
                    </div>
                </div>
            </div>
        </div>
        @include('inspecciones._mensajes')
        @if($inspeccion->estado === 'EN_CAPTURA' && auth()->user()->hasPermission('inspecciones.capturar'))
            <x-ui.card title="Agregar fotografía" description="Puedes usar la cámara del dispositivo o seleccionar una imagen de la galería (JPG, PNG o WebP, máximo 4 MB).">
                <form method="POST" enctype="multipart/form-data" action="{{ route('inspecciones.evidencias.store', $inspeccion->id_inspeccion) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="foto" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Fotografía</label>
                        <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/webp" required class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                    </div>
                    <div class="flex justify-end">
                        <x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="camera" class="h-4 w-4" />Guardar evidencia</span></x-ui.button>
                    </div>
                </form>
                <p class="text-xs text-slate-500 dark:text-slate-400">Si tu dispositivo no permite elegir galería desde este control, usa la opción de archivos del navegador.</p>
                <div class="border-t border-slate-100 pt-5 dark:border-slate-700">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('inspecciones.evidencias.store', $inspeccion->id_inspeccion) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="foto-camara" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Usar la cámara directamente</label>
                            <input type="file" name="foto" id="foto-camara" accept="image/jpeg,image/png,image/webp" capture="environment" required class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:file:bg-slate-800 dark:file:text-slate-200">
                        </div>
                        <div class="flex justify-end">
                            <x-ui.button type="submit" variant="secondary"><span class="inline-flex items-center gap-2"><x-ui.icon name="camera" class="h-4 w-4" />Guardar foto de cámara</span></x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.card>
        @endif
        <x-ui.card title="Fotografías de la inspección" description="La inferencia utiliza el microservicio Cloud de AgroCloud AI. Los resultados originales quedan guardados.">
            <div class="grid gap-5 lg:grid-cols-2">
                @forelse($inspeccion->evidencias as $foto)
                    <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                        <div class="grid grid-cols-2 gap-1 bg-slate-100 dark:bg-slate-800">
                            <div><img src="{{ $foto->imagen_url }}" alt="Fotografía original evidencia {{ $foto->id_evidencia }}" loading="lazy" class="h-48 w-full object-contain"><p class="py-1 text-center text-xs text-slate-500">Original</p></div>
                            <div>@if($foto->imagen_detectada_url)<img src="{{ $foto->imagen_detectada_url }}" alt="Imagen analizada evidencia {{ $foto->id_evidencia }}" loading="lazy" class="h-48 w-full object-contain">@else<div class="grid h-48 place-items-center text-xs text-slate-500">Sin análisis</div>@endif<p class="py-1 text-center text-xs text-slate-500">Resultado IA</p></div>
                        </div>
                        <div class="space-y-3 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2"><p class="font-semibold text-slate-900 dark:text-slate-100">Evidencia #{{ $foto->id_evidencia }}</p><span class="text-xs text-slate-500 dark:text-slate-400">{{ $foto->estado_ia }}</span></div>
                            @if($foto->estado_ia === 'COMPLETADO')
                                <p class="text-sm text-slate-700 dark:text-slate-200">Detecciones: <strong>{{ count($foto->resultado_ia['detections'] ?? []) }}</strong> · Modelo: {{ $foto->version_modelo }}</p>
                                @foreach(($foto->resultado_ia['detections'] ?? []) as $deteccion)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $deteccion['class'] ?? 'Clase desconocida' }} · Confianza {{ number_format((float) ($deteccion['confidence'] ?? 0) * 100, 1) }}%</p>
                                @endforeach
                            @elseif($inspeccion->estado === 'EN_CAPTURA' && auth()->user()->hasPermission('inspecciones.analizar'))
                                <form method="POST" action="{{ route('inspecciones.evidencias.analizar', [$inspeccion->id_inspeccion, $foto->id_evidencia]) }}">@csrf
                                    <button class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ $foto->estado_ia === 'ERROR' ? 'Reintentar análisis' : 'Analizar con IA' }}</button>
                                </form>
                                @if($foto->estado_ia === 'ERROR')<p class="text-xs text-amber-700 dark:text-amber-300">El análisis falló. Puedes reintentar o registrar hallazgos manuales.</p>@endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-slate-200 px-5 py-10 text-center dark:border-slate-700">
                        <div class="mx-auto grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"><x-ui.icon name="camera" class="h-5 w-5" /></div>
                        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Todavía no se han cargado fotografías.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-5 flex justify-end">
                <a href="{{ route('inspecciones.validacion', $inspeccion->id_inspeccion) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                    Continuar a validación <x-ui.icon name="chevron-right" class="h-4 w-4" />
                </a>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
