<x-layouts.app title="Crear muestreo">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <x-ui.back-link href="{{ route('muestreos.index') }}" label="Volver a muestreos" />
                <div class="mt-3 flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                        <x-ui.icon name="clipboard-list" class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Nuevo muestreo</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Registra una muestra sobre un lote existente. No necesitas entrar antes al detalle de lote.</p>
                    </div>
                </div>
            </div>
        </div>
        @include('inspecciones._mensajes')
        <x-ui.card title="Datos de la muestra" description="El tamaño debe responder al protocolo de muestreo aprobado por tu empresa.">
            <form data-ajax-form method="POST" action="{{ route('muestreos.store') }}" class="space-y-5">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="protocolo" label="Protocolo y versión *" placeholder="Ej. Protocolo interno de recepción, v2" required maxlength="200" />
                    <x-ui.input name="poblacion_estimada" label="Población estimada (frutos, opcional)" type="number" min="1" max="100000000" />
                    <label class="text-sm font-medium">Criterio del tamaño de muestra *<textarea name="criterio_muestra" required maxlength="1500" rows="3" placeholder="Justifica el tamaño según el protocolo; no se calcula automáticamente." class="mt-2 w-full rounded-xl border border-slate-300 bg-transparent p-3 dark:border-slate-600"></textarea></label>
                    <label class="text-sm font-medium">Cómo se seleccionaron los frutos *<textarea name="seleccion_frutos" required maxlength="1500" rows="3" placeholder="Describe posiciones, envases o zonas del lote cubiertas." class="mt-2 w-full rounded-xl border border-slate-300 bg-transparent p-3 dark:border-slate-600"></textarea></label>
                </div>
                <div>
                    <label for="id_lote" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Lote <span class="text-red-500">*</span></label>
                    <select required name="id_lote" id="id_lote" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Selecciona un lote</option>
                        @foreach($lotes as $lote)
                            <option value="{{ $lote->id_lote }}" @selected((int) old('id_lote', $loteSeleccionado) === (int) $lote->id_lote)>{{ $lote->codigo_lote }} — {{ $lote->productor?->nombre_razon_social ?? 'Sin productor' }}</option>
                        @endforeach
                    </select>
                    @if($lotes->isEmpty())<p class="mt-2 text-xs text-amber-700 dark:text-amber-300">No hay lotes disponibles. Recepción debe confirmar su ingreso físico antes de crear la muestra.</p>@endif
                </div>
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="tamano_muestra" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Frutos de la muestra *</label>
                        <input required type="number" min="1" max="100000" name="tamano_muestra" id="tamano_muestra" value="{{ old('tamano_muestra') }}" placeholder="Ej. 60" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label for="metodo" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Método de selección *</label>
                        <x-ui.master-select name="metodo" kind="metodo" required />
                    </div>
                </div>
                <div>
                    <label for="observaciones" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="3" maxlength="3000" placeholder="Condiciones de la muestra..." class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">{{ old('observaciones') }}</textarea>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5 dark:border-slate-700">
                    <a wire:navigate href="{{ route('muestreos.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                        Cancelar
                    </a>
                    <x-ui.button type="submit" :disabled="$lotes->isEmpty()">
                        <span class="inline-flex items-center gap-2"><x-ui.icon name="save" class="h-4 w-4" />Guardar muestreo</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
