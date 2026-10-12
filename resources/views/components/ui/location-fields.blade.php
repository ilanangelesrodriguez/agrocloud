@props(['model' => null])
<section data-company-location class="space-y-3">
    <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Ubicación del productor</h3>
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach (['departamento' => 'Departamento', 'provincia' => 'Provincia', 'distrito' => 'Distrito'] as $field => $label)
            <div>
                <label for="producer-{{ $field }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label }}</label>
                <select id="producer-{{ $field }}" name="{{ $field }}" data-geo-field="{{ $field }}" aria-describedby="producer-geo-status" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 disabled:opacity-60">
                    <option value="{{ old($field, $model?->$field) }}">{{ old($field, $model?->$field) ?: 'Selecciona una opción' }}</option>
                </select>
                @error($field)<p class="mt-1 text-xs text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
            </div>
        @endforeach
        <div>
            <label for="producer-ubigeo" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Código UBIGEO</label>
            <input id="producer-ubigeo" name="ubigeo" data-geo-field="ubigeo" readonly value="{{ old('ubigeo', $model?->ubigeo) }}" placeholder="Se completa al elegir distrito" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
        </div>
    </div>
    <p id="producer-geo-status" data-geo-status role="status" class="rounded-xl bg-brand-50 px-3.5 py-2.5 text-xs text-brand-800 dark:bg-brand-900/35 dark:text-brand-200">Cargando ubicaciones…</p>
</section>
