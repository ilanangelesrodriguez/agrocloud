<x-layouts.app title="Editar permiso">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('permisos.index') }}" class="text-sm font-medium text-brand-700">← Volver a permisos</a>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Editar permiso</h2>
        </div>
        <form method="POST" action="{{ route('permisos.update', $permiso) }}">
            @csrf
            @method('PUT')
            <x-ui.card>
                @include('permisos._form')
                <div class="mt-6 flex justify-end"><x-ui.button type="submit">Guardar cambios</x-ui.button></div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
