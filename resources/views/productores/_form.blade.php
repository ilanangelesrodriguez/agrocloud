@php($producer = $productorModel ?? null)
<form method="POST" action="{{ $producer ? route('productores.update', $producer->id_productor) : route('productores.store') }}" data-ajax-form data-company-form class="space-y-5">
    @csrf
    @if ($producer) @method('PUT') @endif
    @include('inspecciones._mensajes')
    <x-ui.card title="Datos del productor" description="Los campos con * son obligatorios.">
        <div class="grid gap-5 sm:grid-cols-[220px_minmax(0,1fr)]">
            <div>
                <x-ui.input name="ruc" label="RUC *" :value="$producer?->ruc" required maxlength="11" minlength="11" inputmode="numeric" pattern="[0-9]{11}" placeholder="11 dígitos" autocomplete="off" />
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Sin espacios ni guiones.</p>
            </div>
            <x-ui.input name="nombre_razon_social" label="Nombre o razón social *" :value="$producer?->nombre_razon_social" required maxlength="200" placeholder="Nombre completo del productor o empresa" />
        </div>
    </x-ui.card>
    <x-ui.card title="Fundo y ubicación" description="Selecciona departamento, provincia y distrito en ese orden. El código UBIGEO se completa automáticamente.">
        <div class="space-y-5">
            <x-ui.input name="fundo" label="Nombre del fundo (opcional)" :value="$producer?->fundo" maxlength="150" placeholder="Ej. Fundo Los Olivos" />
            <x-ui.location-fields :model="$producer" />
        </div>
    </x-ui.card>
    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a wire:navigate href="{{ $producer ? route('productores.show', $producer->id_productor) : route('productores.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Cancelar</a>
        <x-ui.button type="submit"><x-ui.icon name="save" class="mr-2 h-4 w-4" />{{ $producer ? 'Guardar cambios' : 'Registrar productor' }}</x-ui.button>
    </div>
</form>
<script defer src="{{ asset('js/empresa-form.js') }}?v=20261012-geo"></script>
