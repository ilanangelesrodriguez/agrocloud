<x-layouts.app title="Nueva empresa">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="space-y-4">
            <x-ui.back-link :href="route('admin.empresas.index')" label="Regresar al listado de empresas" />
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">Sistema / Empresas / Nuevo registro</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-100 sm:text-3xl">Registrar nueva empresa</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">Completa primero los datos corporativos, luego la ubicación y, si lo deseas, agrega un logo. Al guardar podrás registrar al administrador de la empresa.</p>
            </div>
        </div>
        <form data-ajax-form method="POST" action="{{ route('admin.empresas.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.empresas._form')
            <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">* Los campos obligatorios deben estar completos. El logo es opcional.</p>
                <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <a wire:navigate href="{{ route('admin.empresas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">Cancelar</a>
                    <x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="save" class="h-4 w-4" />Registrar empresa</span></x-ui.button>
                </div>
            </div>
        </form>
    </div>
    <script defer src="{{ asset('js/empresa-form.js') }}?v=20261008-1"></script>
</x-layouts.app>
