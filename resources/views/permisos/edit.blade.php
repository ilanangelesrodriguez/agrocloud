<x-layouts.app title="Editar permiso">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="space-y-2"><x-ui.back-link :href="route('permisos.index')" label="Volver a permisos" /><h2 class="text-2xl font-semibold text-slate-950">Editar permiso global</h2></div>
        <form method="POST" action="{{ route('permisos.update', $permiso) }}">@csrf @method('PUT')<x-ui.card>@include('permisos._form')<div class="mt-6 flex justify-end"><x-ui.button type="submit"><x-ui.icon name="save" class="mr-2 h-4 w-4" />Guardar cambios</x-ui.button></div></x-ui.card></form>
    </div>
</x-layouts.app>
