@props(['verification' => null])
@php
    $formatDate = function ($value, bool $dateOnly = false) {
        if (!$value) return 'No disponible';
        try { $date = \Carbon\Carbon::parse($value); return $dateOnly ? $date->format('d/m/Y') : $date->timezone('America/Lima')->format('d/m/Y H:i').' · Lima'; }
        catch (\Throwable $error) { return 'No disponible'; }
    };
    $result = ucfirst(str_replace('_', ' ', $verification['resultado'] ?? 'pendiente'));
@endphp
<section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <details>
        <summary class="cursor-pointer rounded-lg text-sm focus-visible:outline focus-visible:outline-brand-600"><span class="font-semibold dark:text-slate-100">Verificación tributaria · {{ $result }}</span><span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $verification['mensaje'] ?? 'Sin verificación registrada. Se consultará al guardar o aprobar.' }}</span><span class="mt-2 inline-block text-xs font-semibold text-brand-700 dark:text-brand-300">Consultar detalles del padrón</span></summary>
        <p class="mt-4 text-xs leading-5 text-slate-500">Información del padrón de contribuyentes. No acredita propiedad de fundos o lotes.</p>
        @if($verification)<dl class="mt-4 grid min-w-0 gap-3 text-sm sm:grid-cols-2 xl:grid-cols-1">
            @foreach(['ruc' => 'RUC consultado', 'razon_social' => 'Razón social del padrón', 'estado' => 'Estado SUNAT', 'condicion' => 'Condición SUNAT', 'actualizado' => 'Fecha del padrón', 'consultado_en' => 'Fecha de consulta', 'intentado_en' => 'Último intento'] as $key => $label)
                <div class="min-w-0"><dt class="text-xs text-slate-500">{{ $label }}</dt><dd class="mt-1 break-words [overflow-wrap:anywhere] dark:text-slate-200">{{ in_array($key, ['actualizado', 'consultado_en', 'intentado_en'], true) ? $formatDate($verification[$key] ?? null, $key === 'actualizado') : ($verification[$key] ?? 'No disponible') }}</dd></div>
            @endforeach
        </dl>@endif
    </details>
</section>
