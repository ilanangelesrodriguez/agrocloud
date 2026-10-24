@props(['verification' => null])
<x-ui.card title="Verificación tributaria" description="Información contrastada con el padrón de contribuyentes. No acredita propiedad de fundos o lotes.">
    <p class="text-sm font-semibold dark:text-slate-100">{{ ucfirst(str_replace('_', ' ', $verification['resultado'] ?? 'pendiente')) }}</p>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $verification['mensaje'] ?? 'Sin verificación registrada. Se consultará al guardar o aprobar.' }}</p>
    @if($verification)
        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
            @foreach(['ruc' => 'RUC consultado', 'razon_social' => 'Razón social del padrón', 'estado' => 'Estado SUNAT', 'condicion' => 'Condición SUNAT', 'actualizado' => 'Fecha del padrón', 'consultado_en' => 'Fecha de consulta', 'intentado_en' => 'Último intento'] as $key => $label)
                <div><dt class="text-xs text-slate-500">{{ $label }}</dt><dd class="mt-1 break-words dark:text-slate-200">{{ $verification[$key] ?? 'No disponible' }}</dd></div>
            @endforeach
        </dl>
    @endif
</x-ui.card>
