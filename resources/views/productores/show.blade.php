<x-layouts.app title="Detalle del productor">
    <div class="space-y-5">
        <x-ui.sunat-status :verification="$productorModel->sunat_verificacion" />
        <x-ui.back-link :href="route('productores.index')" label="Volver a productores" />
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $productorModel->nombre_razon_social }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">RUC {{ $productorModel->ruc }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (auth()->user()->hasPermission('productores.lotes.ver'))
                    <a wire:navigate href="{{ route('productores.lotes', $productorModel->id_productor) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Ver lotes ({{ $productorModel->lotes_count }})</a>
                @endif
                @if (auth()->user()->hasPermission('productores.editar'))
                    <a wire:navigate href="{{ route('productores.edit', $productorModel->id_productor) }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700"><x-ui.icon name="pencil" class="h-4 w-4" />Editar productor</a>
                @endif
            </div>
        </div>
        <x-ui.card title="Fundo y ubicación">
            <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (['fundo' => 'Fundo', 'departamento' => 'Departamento', 'provincia' => 'Provincia', 'distrito' => 'Distrito', 'ubigeo' => 'Código UBIGEO'] as $field => $label)
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</dt><dd class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $productorModel->$field ?: 'No registrado' }}</dd></div>
                @endforeach
            </dl>
            @if (!$productorModel->distrito && auth()->user()->hasPermission('productores.editar'))
                <p class="mt-5 rounded-xl bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">La ubicación está incompleta. <a wire:navigate href="{{ route('productores.edit', $productorModel->id_productor) }}" class="font-semibold underline">Completar ubicación</a></p>
            @endif
            @if ($productorModel->ubicacion)
                <details class="mt-5 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <summary class="cursor-pointer text-xs font-medium text-slate-500 dark:text-slate-400">Referencia de ubicación anterior</summary>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $productorModel->ubicacion }}</p>
                </details>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
