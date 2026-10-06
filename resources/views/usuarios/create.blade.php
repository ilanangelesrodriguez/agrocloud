<x-layouts.app title="Nuevo usuario">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="space-y-2">
            <x-ui.back-link :href="route('usuarios.index')" label="Volver a usuarios" />
            <div>
                <h2 class="text-2xl font-semibold text-slate-950">Crear usuario</h2>
                <p class="mt-1 text-sm text-slate-500">La cuenta quedará asociada automáticamente a tu empresa.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('usuarios.store') }}" enctype="multipart/form-data">
            @csrf
            <x-ui.card>
                @include('usuarios._form')
                <div class="mt-6 flex justify-end">
                    <x-ui.button type="submit"><x-ui.icon name="plus" class="mr-2 h-4 w-4" />Crear usuario</x-ui.button>
                </div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
