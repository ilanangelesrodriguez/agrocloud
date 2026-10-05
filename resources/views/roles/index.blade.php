<x-layouts.app title="Roles">
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-slate-950">Roles y acceso</h2>
            <p class="mt-1 text-sm text-slate-500">Cada rol agrupa permisos. El rol ADMIN conserva acceso total como protección administrativa.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($roles as $rol)
                <x-ui.card>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-700">{{ $rol->codigo }}</p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">{{ $rol->nombre }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $rol->descripcion }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-600">{{ $rol->estado ? 'Activo' : 'Inactivo' }}</span>
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-sm text-slate-500">
                        <span>{{ $rol->usuarios_count }} usuarios</span>
                        <span>{{ $rol->permisos_count }} permisos</span>
                    </div>

                    @if (auth()->user()->hasPermission('roles.editar'))
                        <a href="{{ route('roles.edit', $rol) }}" class="mt-4 inline-flex rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Configurar permisos</a>
                    @endif
                </x-ui.card>
            @endforeach
        </div>
    </div>
</x-layouts.app>
