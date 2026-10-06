<x-layouts.app title="Nueva empresa">
    <div class="mx-auto max-w-5xl space-y-6">
        <div><x-ui.back-link :href="route('admin.empresas.index')" label="Regresar a empresas" /><div class="mt-3"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700">Administración global</p><h2 class="mt-2 text-2xl font-semibold text-slate-950">Registrar empresa</h2><p class="mt-1 text-sm text-slate-500">Crea un nuevo tenant. Después podrás registrar su administrador y demás usuarios.</p></div></div>
        <form method="POST" action="{{ route('admin.empresas.store') }}" enctype="multipart/form-data">@csrf<x-ui.card>@include('admin.empresas._form')<div class="mt-8 flex flex-col-reverse gap-2 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end"><a href="{{ route('admin.empresas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a><x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="save" class="h-4 w-4" />Registrar empresa</span></x-ui.button></div></x-ui.card></form>
    </div>
</x-layouts.app>
