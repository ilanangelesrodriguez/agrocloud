<x-layouts.app title="Editar empresa">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="space-y-4">
            <x-ui.back-link :href="route('admin.empresas.show', $empresa)" label="Regresar al detalle de la empresa" />
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700">Sistema / Empresas / Edición</p>
                    <h2 class="mt-2 break-words text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Editar {{ $empresa->nombre_comercial }}</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">Actualiza los datos corporativos, ubicación y logo. Si no seleccionas una imagen nueva, conservarás la actual.</p>
                </div>
                <div class="shrink-0"><x-ui.status-badge :active="$empresa->estado" active-label="Empresa activa" inactive-label="Empresa inactiva" /></div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.empresas.update', $empresa) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.empresas._form')
            <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p class="text-xs leading-5 text-slate-500">Revisa los cambios antes de guardarlos. El logo solo se reemplaza al seleccionar otro archivo.</p>
                <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <a href="{{ route('admin.empresas.show', $empresa) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                    <x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="save" class="h-4 w-4" />Guardar cambios</span></x-ui.button>
                </div>
            </div>
        </form>
    </div>
    <script defer src="{{ asset('js/empresa-form.js') }}?v=20261008-1"></script>
</x-layouts.app>
