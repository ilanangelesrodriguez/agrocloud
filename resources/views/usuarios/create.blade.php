<x-layouts.app title="Nuevo usuario">
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <a href="{{ route('usuarios.index') }}" class="text-sm font-medium text-brand-700">← Volver a usuarios</a>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">Crear usuario</h2>
        </div>

        <form method="POST" action="{{ route('usuarios.store') }}" enctype="multipart/form-data">
            @csrf
            <x-ui.card>
                @include('usuarios._form')
                <div class="mt-6 flex justify-end"><x-ui.button type="submit">Crear usuario</x-ui.button></div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
