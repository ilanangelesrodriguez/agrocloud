@php
    $recepcion = $lote->recepcion;
    $puedeConfirmar = !$recepcion && !$lote->muestreos()->exists() && in_array($lote->estado->value, ['PENDIENTE_RECEPCION', 'RECIBIDO'], true) && auth()->user()->rol?->codigo === 'RECEPCION' && auth()->user()->hasPermission('recepciones.crear');
@endphp
<div class="space-y-5">
    <x-ui.card :title="$lote->codigo_lote" description="Datos originales del lote, de solo lectura."><dl class="grid gap-4 text-sm sm:grid-cols-2">@foreach(['Productor' => $lote->productor?->nombre_razon_social, 'Producto' => $lote->producto.' '.$lote->variedad, 'Peso declarado' => number_format((float) $lote->peso_kg, 2).' kg'] as $label => $value)<div><dt class="text-slate-500">{{ $label }}</dt><dd class="mt-1 font-semibold dark:text-slate-100">{{ $value ?? 'No registrado' }}</dd></div>@endforeach<div><dt class="text-slate-500">Estado</dt><dd class="mt-1"><x-ui.state-badge :state="$lote->estado" /></dd></div></dl></x-ui.card>
    @if($recepcion)
        <x-ui.card title="Ingreso confirmado"><dl class="space-y-3 text-sm">@foreach(['Documento' => $recepcion->documento, 'Peso recibido' => number_format((float) $recepcion->peso_recibido_kg, 2).' kg', 'Diferencia con lo declarado' => number_format((float) $recepcion->peso_recibido_kg - (float) $lote->peso_kg, 2).' kg', 'Fecha y hora' => $recepcion->recibido_en?->format('d/m/Y H:i'), 'Transportista' => $recepcion->transportista, 'Placa' => $recepcion->placa, 'Responsable' => $recepcion->responsable?->nombre_completo, 'Condiciones de ingreso' => $recepcion->observaciones] as $label => $value)<div><dt class="text-slate-500">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line dark:text-slate-100">{{ $value ?: 'No registrado' }}</dd></div>@endforeach</dl></x-ui.card>
    @elseif($puedeConfirmar)
        <form data-ajax-form @if(request()->header('X-Workspace-Drawer')) data-workspace-drawer-form @endif data-declared-weight="{{ $lote->peso_kg }}" data-confirm="Se confirmará el ingreso físico de este lote y quedará disponible para muestreo. Comprueba el peso, documento y fecha antes de continuar." data-confirm-title="Confirmar ingreso físico" data-confirm-label="Confirmar recepción" method="POST" action="{{ route('recepciones.store', $lote->id_lote) }}" class="space-y-4">
            @csrf
            <h3 class="font-semibold dark:text-slate-100">Datos de recepción</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="documento" label="Documento de ingreso *" required maxlength="100" />
                <x-ui.input name="peso_recibido_kg" label="Peso recibido (kg) *" type="number" :value="$lote->peso_kg" min="0.01" step="0.01" max="99999999.99" required />
                <x-ui.input name="recibido_en" label="Fecha y hora de recepción *" type="datetime-local" :value="now()->format('Y-m-d\TH:i')" :max="now()->format('Y-m-d\TH:i')" required />
                <x-ui.input name="transportista" label="Transportista (opcional)" maxlength="150" />
                <x-ui.input name="placa" label="Placa (opcional)" maxlength="20" />
            </div>
            <p data-weight-difference role="status" aria-live="polite" class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800 dark:text-slate-200"></p>
            <label class="block text-sm font-medium dark:text-slate-200">Condiciones de ingreso y diferencias de peso <span data-weight-note-required hidden class="text-amber-700 dark:text-amber-300">* Obligatorio por variación de peso</span><textarea name="observaciones" rows="3" maxlength="3000" placeholder="Si cambia el peso, explica la variación. Describe también las condiciones de ingreso." class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-3 dark:border-slate-600 dark:bg-slate-900"></textarea></label>
            <div class="sticky bottom-0 flex flex-wrap justify-end gap-3 border-t border-slate-200 bg-white py-4 dark:border-slate-700 dark:bg-slate-900">@if(request()->header('X-Workspace-Drawer'))<button type="button" data-workspace-close class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold dark:border-slate-600">Cancelar</button>@else<a wire:navigate href="{{ route('recepciones.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold dark:border-slate-600">Volver</a>@endif<x-ui.button type="submit">Confirmar recepción</x-ui.button></div>
        </form>
    @else
        <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500 dark:bg-slate-800">La confirmación corresponde al personal de recepción y solo está disponible antes de iniciar el muestreo.</p>
    @endif
    @if(auth()->user()->hasPermission('lotes.ver'))<a wire:navigate href="{{ route('lotes.show', $lote->id_lote) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 dark:text-brand-300">Ver detalle del lote <x-ui.icon name="chevron-right" class="h-4 w-4" /></a>@endif
</div>
