<x-layouts.app title="Crear muestreo">
<div class="space-y-6">
    <div><x-ui.back-link :href="route('muestreos.index')" label="Volver a muestreos" /><h1 class="mt-3 text-2xl font-semibold dark:text-slate-100">Nuevo muestreo</h1><p class="mt-1 text-sm text-slate-500">Selecciona el lote recibido y documenta el protocolo antes de identificar los frutos.</p></div>
    <x-ui.steps :steps="['Lote recibido', 'Protocolo', 'Muestra', 'Revisión']" />
    <form data-ajax-form data-sampling-form method="POST" action="{{ route('muestreos.store') }}" class="space-y-5">
        @csrf
        <x-ui.card title="1. Lote recibido" description="Solo aparecen lotes con ingreso físico confirmado y sin una muestra inicial.">
            <label class="block text-sm font-medium dark:text-slate-200">Filtrar lotes disponibles<input type="search" data-option-search="id_lote" placeholder="Código, productor o producto" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"></label>
            <label class="mt-4 block text-sm font-medium dark:text-slate-200">Lote *<select id="id_lote" name="id_lote" required class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"><option value="">Selecciona un lote</option>@foreach($lotes as $lote)<option value="{{ $lote->id_lote }}" data-info="{{ $lote->producto.' '.$lote->variedad.' · '.$lote->productor?->nombre_razon_social.' · Peso recibido: '.number_format((float) $lote->peso_operativo_kg, 2).' kg · Peso declarado: '.number_format((float) $lote->peso_kg, 2).' kg · Recepción confirmada' }}" @selected((int) old('id_lote', $loteSeleccionado) === (int) $lote->id_lote)>{{ $lote->codigo_lote }} · {{ $lote->producto }} · {{ $lote->productor?->nombre_razon_social }}</option>@endforeach</select></label>
            <p data-lot-summary class="mt-3 rounded-xl bg-slate-50 p-3 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300" aria-live="polite"></p>
            @if($lotes->isEmpty())<p class="mt-3 text-sm text-amber-700 dark:text-amber-300">Recepción debe confirmar un ingreso antes de crear su muestra.</p>@endif
        </x-ui.card>
        <x-ui.card title="2. Protocolo y cobertura" description="Conserva la versión aplicada y la justificación técnica; no se calcula un tamaño arbitrario.">
            <div class="grid gap-4 sm:grid-cols-2"><x-ui.input name="protocolo" label="Protocolo y versión *" required maxlength="200" /><x-ui.input name="poblacion_estimada" label="Población estimada de frutos (opcional)" type="number" min="1" max="100000000" />
            @foreach(['criterio_muestra' => 'Criterio del tamaño de muestra', 'seleccion_frutos' => 'Selección de frutos y cobertura del lote'] as $name => $label)<label class="text-sm font-medium dark:text-slate-200">{{ $label }} *<textarea name="{{ $name }}" required maxlength="1500" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-3 dark:border-slate-600 dark:bg-slate-900">{{ old($name) }}</textarea></label>@endforeach</div>
        </x-ui.card>
        <x-ui.card title="3. Muestra e identificación" description="Cada fruto recibirá un código y podrá tener una o varias fotografías.">
            <div class="grid gap-4 sm:grid-cols-2"><x-ui.input name="tamano_muestra" label="Cantidad de frutos *" type="number" required min="1" max="100000" /><x-ui.master-select name="metodo" kind="metodo" label="Método de selección *" required /></div>
            <label class="mt-4 block text-sm font-medium dark:text-slate-200">Observaciones<textarea name="observaciones" maxlength="3000" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-3 dark:border-slate-600 dark:bg-slate-900">{{ old('observaciones') }}</textarea></label>
        </x-ui.card>
        <x-ui.card title="4. Revisión antes de registrar"><p data-sampling-summary aria-live="polite" class="text-sm text-slate-600 dark:text-slate-300">Completa el lote, protocolo, método y cantidad para revisar la muestra.</p><p class="mt-3 text-xs text-slate-500">Después de guardar podrás iniciar la inspección según tu rol y permisos.</p></x-ui.card>
        <div class="flex flex-wrap justify-end gap-3"><a wire:navigate href="{{ route('muestreos.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm dark:border-slate-600 dark:text-slate-200">Cancelar</a><x-ui.button type="submit" :disabled="$lotes->isEmpty()">Registrar muestra y continuar</x-ui.button></div>
    </form>
</div>
</x-layouts.app>
