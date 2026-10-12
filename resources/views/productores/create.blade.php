<x-layouts.app title="Registrar productor">
    <div class="space-y-5">
        <div>
            <x-ui.back-link :href="route('productores.index')" label="Volver a productores" />
            <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-100">Registrar productor</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Registra al productor y la ubicación de su fundo para asociar sus lotes.</p>
        </div>
        @include('productores._form')
    </div>
</x-layouts.app>
