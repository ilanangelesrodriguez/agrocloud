<x-layouts.app title="Procedimientos de muestreo">
<div class="space-y-6">
    <x-ui.back-link :href="route('maestros.index')" label="Volver a catálogos" />
    <h1 class="text-2xl font-semibold dark:text-slate-100">Procedimientos de muestreo</h1>
    <p class="text-sm text-slate-500">Registra procedimientos autorizados por tu empresa, aplicables al producto, riesgo y destino. El sistema no inventa tamaños de muestra. Cada cambio requiere una versión nueva.</p>
    <x-ui.card title="Registrar procedimiento autorizado"><form method="POST" action="{{ route('sampling.procedures.store') }}" class="grid gap-4 sm:grid-cols-2">@csrf
        <x-ui.input name="nombre" label="Nombre" maxlength="150" required />
        <x-ui.input name="version" label="Versión" maxlength="30" required />
        <x-ui.master-select name="metodo" kind="metodo" label="Forma de seleccionar los frutos" required />
        <x-ui.input name="max_frutos" label="Límite de frutos por muestra" type="number" min="1" :max="config('operations.sampling_max_fruits')" required />
        <x-ui.input name="tamano_recomendado" label="Cantidad recomendada (solo si el procedimiento la define)" type="number" min="1" :max="config('operations.sampling_max_fruits')" />
        @foreach(['criterio_muestra' => '¿Por qué se eligió esta cantidad?', 'seleccion_frutos' => '¿De qué partes del lote se tomarán los frutos?'] as $name => $label)<label class="text-sm dark:text-slate-200">{{ $label }}<textarea name="{{ $name }}" required maxlength="1500" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-3 dark:border-slate-600 dark:bg-slate-900">{{ old($name) }}</textarea></label>@endforeach
        <div><x-ui.button type="submit">Guardar procedimiento</x-ui.button></div>
    </form></x-ui.card>
    <x-ui.card title="Versiones registradas"><ul class="space-y-4">@foreach($procedures as $item)<li class="rounded-xl border border-slate-200 p-4 dark:border-slate-700 dark:text-slate-200"><p class="font-semibold">{{ $item->nombre }} · {{ $item->version }} · {{ $item->activo ? 'Disponible' : 'Inactivo' }}</p><p class="mt-2 text-sm">{{ $item->metodo }} · Límite: {{ $item->max_frutos }} frutos · Recomendado: {{ $item->tamano_recomendado ?? 'Según criterio documentado' }}</p><p class="mt-2 text-sm">{{ $item->criterio_muestra }}</p><p class="mt-2 text-sm">{{ $item->seleccion_frutos }}</p><form method="POST" action="{{ route('sampling.procedures.toggle', $item) }}" class="mt-3">@csrf @method('PATCH')<x-ui.button type="submit" variant="secondary">{{ $item->activo ? 'Desactivar' : 'Activar' }}</x-ui.button></form></li>@endforeach</ul>{{ $procedures->links() }}</x-ui.card>
</div>
</x-layouts.app>
