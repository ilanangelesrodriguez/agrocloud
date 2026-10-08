<x-layouts.app title="Nuevo usuario">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="space-y-4">
            <x-ui.back-link :href="route('usuarios.index')" label="Volver a usuarios" />
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">Usuarios / Nuevo registro</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-100 sm:text-3xl">Crear usuario</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">La cuenta quedará asociada automáticamente a tu empresa.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('usuarios.store') }}" enctype="multipart/form-data">
            @csrf
            @include('usuarios._form')
            <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-end sm:px-6">
                <a href="{{ route('usuarios.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">Cancelar</a>
                <x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="user-plus" class="h-4 w-4" />Crear usuario</span></x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
