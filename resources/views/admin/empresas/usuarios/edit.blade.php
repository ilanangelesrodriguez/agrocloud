<x-layouts.app title="Editar usuario de empresa">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="space-y-4">
            <x-ui.back-link :href="route('admin.empresas.usuarios.index', $empresa)" label="Regresar a usuarios" />
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">{{ $empresa->nombre_comercial }} / Usuarios / Edición</p>
                    <h2 class="mt-2 break-words text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-100 sm:text-3xl">Editar {{ $usuario->nombre_completo }}</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">Actualiza los datos, rol, estado, foto o contraseña del usuario.</p>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.empresas.usuarios.update', [$empresa,$usuario]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('usuarios._form')
            <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-end sm:px-6">
                <a href="{{ route('admin.empresas.usuarios.index', $empresa) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">Cancelar</a>
                <x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="save" class="h-4 w-4" />Guardar cambios</span></x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
