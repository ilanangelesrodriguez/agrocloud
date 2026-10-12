<x-layouts.app title="Editar productor">
    <div class="space-y-5">
        <div>
            <x-ui.back-link :href="route('productores.index')" label="Volver a productores" />
            <h1 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-100">Editar productor</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Actualiza sus datos y la ubicación del fundo.</p>
        </div>
        @include('productores._form')
    </div>
</x-layouts.app>
