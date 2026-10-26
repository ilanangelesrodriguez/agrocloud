@php
    $detectados = $foto->resultado_ia['detections'] ?? [];
    $completa = $foto->estado_ia === 'COMPLETADO';
    $puedeAnalizar = $foto->pertinencia !== 'INVALIDA' && $inspeccion->estado === 'EN_CAPTURA'
        && auth()->user()->rol?->codigo === 'INSPECTOR'
        && auth()->user()->hasPermission('inspecciones.analizar')
        && (!$inspeccion->id_inspector || (int) $inspeccion->id_inspector === (int) auth()->id());
    $reanalizarAtascado = $foto->estado_ia === 'PROCESANDO'
        && $foto->updated_at?->lt(now()->subMinutes(4));
    $resumen = $foto->resultado_ia['summary'] ?? [];
@endphp
<article data-usable="{{ $foto->pertinencia === 'INVALIDA' ? '0' : '1' }}" data-fruto-id="{{ $foto->id_fruto }}" data-fruto-numero="{{ $foto->fruto?->numero }}" data-reviewed="{{ isset($revisiones[$foto->id_evidencia]) ? '1' : '0' }}" data-evidencia-id="{{ $foto->id_evidencia }}" data-analyzed="{{ $completa ? '1' : '0' }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition dark:border-slate-700 dark:bg-slate-900">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3 dark:border-slate-700 sm:px-5">
        <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Evidencia #{{ $foto->id_evidencia }}</p>
            <p class="text-xs font-mono text-brand-700">{{ $foto->fruto?->codigo ?? 'Evidencia histórica sin fruto individual' }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $foto->created_at?->copy()->timezone('America/Lima')->format('d/m/Y H:i') }}</p>
        </div>
        <x-ui.state-badge :state="$foto->estado_ia" :label="match ($foto->estado_ia) { 'COMPLETADO' => 'Analizada', 'PROCESANDO' => 'Procesando', 'ERROR' => 'Error IA', default => 'Pendiente de IA' }" />
    </div>
    <div class="flex gap-2 px-4 py-2 md:hidden"><button type="button" data-photo-view="original" aria-pressed="true" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold dark:border-slate-700">Original</button><button type="button" data-photo-view="resultado" aria-pressed="false" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold dark:border-slate-700">Resultado IA</button></div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-slate-200 dark:bg-slate-700">
        <div data-photo-panel="original" class="min-w-0 bg-slate-50 p-2 dark:bg-slate-800 md:block">
            <div class="flex h-40 items-center justify-center sm:h-48">
                <img data-image-zoom data-image-src="{{ $foto->imagen_url }}" data-image-group="{{ $foto->id_evidencia }}" data-image-kind="original" tabindex="0" role="button" aria-label="Ampliar imagen original" src="{{ $foto->imagen_url }}" alt="Imagen original evidencia {{ $foto->id_evidencia }}" loading="lazy" class="max-h-full max-w-full rounded-lg object-contain">
            </div>
            <p class="mt-2 text-center text-xs font-medium text-slate-500 dark:text-slate-400">Foto original</p>
        </div>
        <div data-photo-panel="resultado" class="hidden min-w-0 bg-slate-50 p-2 dark:bg-slate-800 md:block">
            <div class="flex h-40 items-center justify-center sm:h-48">
                @if($foto->imagen_detectada_url)
                    <img data-image-zoom data-image-src="{{ $foto->imagen_detectada_url }}" data-image-group="{{ $foto->id_evidencia }}" data-image-kind="resultado" tabindex="0" role="button" aria-label="Ampliar resultado IA" src="{{ $foto->imagen_detectada_url }}" alt="Imagen IA con detecciones de evidencia {{ $foto->id_evidencia }}" loading="lazy" class="max-h-full max-w-full rounded-lg object-contain">
                @else
                    <div class="flex flex-col items-center gap-2 text-slate-400"><x-ui.icon name="search" class="h-7 w-7" /><span class="text-xs">Pendiente de análisis</span></div>
                @endif
            </div>
            <p class="mt-2 text-center text-xs font-medium text-slate-500 dark:text-slate-400">Resultado IA</p>
        </div>
    </div>
    @if($foto->pertinencia === 'INVALIDA')<p class="border-t border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200"><strong>Descartada para evaluación.</strong> {{ $foto->motivo_descarte }} Se conserva como historial y no cubre este fruto; registra una fotografía de reemplazo.</p>@endif
    <div class="space-y-3 px-4 py-4 sm:px-5">
        <div class="flex flex-wrap items-center gap-2 text-xs"><span class="text-slate-500">Revisión humana:</span><x-ui.state-badge :state="isset($revisiones[$foto->id_evidencia]) ? 'VALIDADA' : 'PENDIENTE'" :label="isset($revisiones[$foto->id_evidencia]) ? 'Revisada' : 'Pendiente de revisión'" /></div>
        @if($completa)
            <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                <p class="font-semibold text-slate-800 dark:text-slate-100">{{ count($detectados) }} {{ count($detectados) === 1 ? 'detección' : 'detecciones' }}</p>
                <span class="text-xs text-slate-500 dark:text-slate-400">Modelo {{ $foto->version_modelo ?: 'v1' }}</span>
            </div>
            @if($resumen)
                <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-50 p-3 text-xs dark:bg-slate-800 sm:grid-cols-5">
                    <div><p class="text-slate-500 dark:text-slate-400">Total</p><p class="mt-0.5 font-semibold text-slate-900 dark:text-slate-100">{{ $resumen['total_detections'] ?? count($detectados) }}</p></div>
                    <div><p class="text-slate-500 dark:text-slate-400">Saltamontes</p><p class="mt-0.5 font-semibold text-slate-900 dark:text-slate-100">{{ $resumen['grasshoppers'] ?? 0 }}</p></div>
                    <div><p class="text-slate-500 dark:text-slate-400">Orugas</p><p class="mt-0.5 font-semibold text-slate-900 dark:text-slate-100">{{ $resumen['caterpillars'] ?? 0 }}</p></div>
                    <div><p class="text-slate-500 dark:text-slate-400">Confianza promedio</p><p class="mt-0.5 font-semibold text-slate-900 dark:text-slate-100">{{ number_format(((float) ($resumen['average_confidence'] ?? 0)) * 100, 1) }}%</p></div>
                    <div><p class="text-slate-500 dark:text-slate-400">Confianza máxima</p><p class="mt-0.5 font-semibold text-slate-900 dark:text-slate-100">{{ number_format(((float) ($resumen['max_confidence'] ?? 0)) * 100, 1) }}%</p></div>
                </div>
            @endif
            @if(count($detectados) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($detectados as $deteccion)
                        <span class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $deteccion['class'] ?? 'Sin clase' }} · {{ number_format(((float) ($deteccion['confidence'] ?? 0)) * 100, 1) }}%</span>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-600 dark:text-slate-300">El modelo no detectó objetos sobre su umbral. Esto no garantiza ausencia de problemas.</p>
            @endif
        @elseif($foto->estado_ia === 'ERROR')
            <p class="text-sm text-red-700 dark:text-red-300">{{ $foto->error_ia ?: 'Falló la solicitud. Puedes reintentar.' }}</p>
        @elseif($foto->estado_ia === 'PROCESANDO')
            <p class="text-sm text-blue-700 dark:text-blue-300">Análisis iniciado. Espera unos momentos.</p>
        @else
            <p class="text-xs text-slate-500 dark:text-slate-400">La fotografía está guardada. Solicita el análisis para ver las predicciones.</p>
        @endif
        @if(!$completa && $puedeAnalizar)
            <form method="POST" data-ia-form action="{{ route('inspecciones.evidencias.analizar', [$inspeccion->id_inspeccion, $foto->id_evidencia]) }}">
                @csrf
                <button type="submit" {{ $foto->estado_ia === 'PROCESANDO' && !$reanalizarAtascado ? 'disabled' : '' }} class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                    <x-ui.icon name="search" class="h-4 w-4" />{{ $foto->estado_ia === 'ERROR' || $reanalizarAtascado ? 'Reintentar análisis IA' : 'Analizar con IA' }}
                </button>
            </form>
        @endif
    </div>
</article>
