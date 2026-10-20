@props(['state', 'label' => null])
@php
    $code = strtoupper($state instanceof \BackedEnum ? $state->value : (string) $state);
    $tone = match ($code) {
        'ACTIVO', 'ACTIVA', 'VALIDADO', 'VALIDADA', 'CLASIFICADO', 'APROBADO', 'COMPLETADO', 'CONFIRMADA', 'RESUELTA', 'CERRADA' => 'success',
        'EN_MUESTREO', 'EN_INSPECCION', 'EN_CAPTURA', 'PROCESANDO', 'EN_ATENCION' => 'progress',
        'PENDIENTE', 'PENDIENTE_RECEPCION', 'PENDIENTE_VALIDACION', 'PENDIENTE_DICTAMEN', 'OBSERVADO', 'SIN_VALIDAR', 'ABIERTA', 'MEDIA', 'RETENIDO' => 'warning',
        'RECHAZADO', 'ERROR', 'DESCARTADA', 'ALTA', 'CRITICA' => 'danger',
        default => 'neutral',
    };
    $colors = match ($tone) {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-800',
        'progress' => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-800',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-800',
        'danger' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-800',
        default => 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    };
    $text = $label ?? match ($code) {
        'RETENIDO' => 'Retenido por incidencia',
        'CRITICA' => 'Crítica', 'EN_ATENCION' => 'En atención',
        'PENDIENTE_RECEPCION' => 'Pendiente de recepción', 'CLASIFICADO' => 'Aprobado', 'EN_MUESTREO' => 'En muestreo', 'EN_INSPECCION' => 'En inspección',
        'EN_CAPTURA' => 'En captura', 'PENDIENTE_VALIDACION' => 'Pendiente de dictamen',
        'PENDIENTE_DICTAMEN' => 'Pendiente de dictamen',
        'SIN_VALIDAR' => 'Sin validar',
        default => ucfirst(mb_strtolower(str_replace('_', ' ', $code ?: 'Sin estado'))),
    };
@endphp
<span {{ $attributes->class("inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$colors}") }}>
    <span aria-hidden="true" class="h-1.5 w-1.5 shrink-0 rounded-full bg-current"></span>
    {{ $text }}
</span>
