<x-layouts.app title="Editar permiso">
    <div class="space-y-6">
        <div class="space-y-2"><x-ui.back-link :href="route('permisos.index')" label="Volver a permisos" /><h2 class="text-2xl font-semibold text-slate-950 dark:text-slate-100">Editar permiso global</h2></div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200"><p class="font-semibold">Este permiso está asignado a {{ $permiso->roles_count }} roles</p><p class="mt-1">{{ $permiso->roles->pluck('nombre')->join(', ') ?: 'Sin roles asignados.' }}</p><p class="mt-2">Los cambios afectan a todas las empresas. Modificar el código técnico puede impedir que las acciones de la aplicación reconozcan este permiso.</p></div>
        <form data-ajax-form data-confirm="Modificar este permiso afectará a los roles que lo utilizan en todas las empresas. Comprueba el código y el estado antes de guardar." data-confirm-title="Modificar permiso global" data-confirm-label="Guardar cambios" method="POST" action="{{ route('permisos.update', $permiso) }}">@csrf @method('PUT')<x-ui.card>@include('permisos._form')<div class="mt-6 flex justify-end"><x-ui.button type="submit"><x-ui.icon name="save" class="mr-2 h-4 w-4" />Guardar cambios</x-ui.button></div></x-ui.card></form>
    </div>
</x-layouts.app>
