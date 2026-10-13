<x-layouts.app :title="$definition['label']">
    <div class="space-y-5">
        <x-ui.back-link :href="route('maestros.show', $tipo)" label="Volver al catálogo" />
        <div><h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $item ? 'Editar opción' : 'Agregar opción' }} · {{ $definition['label'] }}</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ auth()->user()->isSuperAdmin() ? 'Opción global para todas las empresas.' : 'Opción disponible solo para tu empresa.' }}</p></div>
        <form data-ajax-form method="POST" action="{{ $item ? route('maestros.update', [$tipo, $item]) : route('maestros.store', $tipo) }}" class="space-y-5">
            @csrf @if($item) @method('PUT') @endif
            <x-ui.card>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input name="nombre" label="Nombre *" :value="$item?->nombre" required maxlength="150" />
                    @if(isset($definition['parent']))
                        <x-ui.select name="parent_id" label="Producto *" required><option value="">Selecciona un producto</option>@foreach($parents as $parent)<option value="{{ $parent->id }}" @selected((int) old('parent_id', $item?->parent_id) === $parent->id)>{{ $parent->nombre }}</option>@endforeach</x-ui.select>
                    @endif
                    @if($tipo === 'unidad')<x-ui.input name="codigo" label="Código de unidad *" :value="$item?->codigo" required maxlength="40" placeholder="Ej. kg, g o t" />@endif
                    <div class="sm:col-span-2"><label for="descripcion" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Descripción (opcional)</label><textarea name="descripcion" id="descripcion" rows="3" maxlength="1000" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">{{ old('descripcion', $item?->descripcion) }}</textarea></div>
                </div>
            </x-ui.card>
            <div class="flex justify-end gap-3"><a wire:navigate href="{{ route('maestros.show', $tipo) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold dark:border-slate-600">Cancelar</a><x-ui.button type="submit">Guardar opción</x-ui.button></div>
        </form>
    </div>
</x-layouts.app>
