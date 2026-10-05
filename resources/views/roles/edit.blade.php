<x-layouts.app title="Permisos del rol">
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <a href="{{ route('roles.index') }}" class="text-sm font-medium text-brand-700">← Volver a roles</a>
            <h2 class="mt-2 text-2xl font-semibold text-slate-950">{{ $rol->nombre }}</h2>
            <p class="mt-1 text-sm text-slate-500">Marca las operaciones que podrá realizar este rol.</p>
        </div>

        <form method="POST" action="{{ route('roles.update', $rol) }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                @foreach ($permisos as $modulo => $items)
                    <x-ui.card :title="$modulo">
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($items as $permiso)
                                <label class="flex gap-3 rounded-xl border border-slate-200 p-4">
                                    <input type="checkbox" name="permisos[]" value="{{ $permiso->id_permiso }}"
                                        @checked($rol->permisos->contains('id_permiso', $permiso->id_permiso))
                                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span>
                                        <span class="block text-sm font-medium text-slate-900">{{ $permiso->nombre }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $permiso->codigo }} · {{ $permiso->estado ? 'activo' : 'inactivo' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </x-ui.card>
                @endforeach

                @if ($rol->codigo !== 'ADMIN')
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="estado" value="0">
                        <input type="checkbox" name="estado" value="1" @checked(old('estado', $rol->estado)) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Rol activo
                    </label>
                @else
                    <input type="hidden" name="estado" value="1">
                @endif

                <div class="flex justify-end"><x-ui.button type="submit">Guardar permisos</x-ui.button></div>
            </div>
        </form>
    </div>
</x-layouts.app>
