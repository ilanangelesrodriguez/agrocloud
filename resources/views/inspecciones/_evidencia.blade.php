@php
    $detectados = $foto->resultado_ia['detections'] ?? [];
    $completa = $foto->estado_ia === 'COMPLETADO';
    $puedeAnalizar = $inspeccion->estado === 'EN_CAPTURA'
        && auth()->user()->rol?->codigo === 'INSPECTOR'
        && auth()->user()->hasPermission('inspecciones.analizar');
    $reanalizarAtascado = $foto->estado_ia === 'PROCESANDO'
        && $foto->updated_at?->lt(now()->subMinutes(4));
    $resumen = $foto->resultado_ia['summary'] ?? [];
@endphp
<article data-evidencia-id="{{ $foto->id_evidencia }}" data-analyzed="{{ $completa ? '1' : '0' }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition dark:border-slate-700 dark:bg-slate-900">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3 dark:border-slate-700 sm:px-5">
        <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Evidencia #{{ $foto->id_evidencia }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $foto->created_at?->format('d/m/Y H:i') }}</p>
        </div>
        <span class="rounded-full px-2.5 py-1 text-xs font-semibold
            @if($completa) bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300
            @elseif($foto->estado_ia === 'ERROR') bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300
            @elseif($foto->estado_ia === 'PROCESANDO') bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
            @else bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 @endif">
            {{ match ($foto->estado_ia) { 'COMPLETADO' => 'Analizada', 'PROCESANDO' => 'Procesando', 'ERROR' => 'Error IA', default => 'Pendiente de IA' } }}
        </span>
    </div>
    <div class="grid grid-cols-2 gap-px bg-slate-200 dark:bg-slate-700">
        <div class="min-w-0 bg-slate-50 p-2 dark:bg-slate-800">
            <div class="flex h-40 items-center justify-center sm:h-48">
                <img src="{{ $foto->imagen_url }}" alt="Imagen original evidencia {{ $foto->id_evidencia }}" loading="lazy" class="max-h-full max-w-full rounded-lg object-contain">
            </div>
            <p class="mt-2 text-center text-xs font-medium text-slate-500 dark:text-slate-400">Foto original</p>
        </div>
        <div class="min-w-0 bg-slate-50 p-2 dark:bg-slate-800">
            <div class="flex h-40 items-center justify-center sm:h-48">
                @if($foto->imagen_detectada_url)
                    <img src="{{ $foto->imagen_detectada_url }}" alt="Imagen IA con detecciones de evidencia {{ $foto->id_evidencia }}" loading="lazy" class="max-h-full max-w-full rounded-lg object-contain">
                @else
                    <div class="flex flex-col items-center gap-2 text-slate-400"><x-ui.icon name="search" class="h-7 w-7" /><span class="text-xs">Pendiente de análisis</span></div>
                @endif
            </div>
            <p class="mt-2 text-center text-xs font-medium text-slate-500 dark:text-slate-400">Resultado IA</p>
        </div>
    </div>
    <div class="space-y-3 px-4 py-4 sm:px-5">
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
