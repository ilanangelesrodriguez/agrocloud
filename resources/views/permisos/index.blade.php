<x-layouts.app title="Permisos">
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-slate-950">Catálogo de permisos</h2>
                <p class="mt-1 text-sm text-slate-500">Los códigos se usan en el backend para proteger rutas y acciones.</p>
            </div>
            @if (auth()->user()->hasPermission('permisos.crear'))
                <a href="{{ route('permisos.create') }}" class="inline-flex rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Nuevo permiso</a>
            @endif
        </div>

        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Módulo</th>
                            <th class="px-3 py-3">Permiso</th>
                            <th class="px-3 py-3">Código</th>
                            <th class="px-3 py-3">Roles</th>
                            <th class="px-3 py-3">Estado</th>
                            <th class="px-3 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($permisos as $permiso)
                            <tr>
                                <td class="px-3 py-4 text-slate-600">{{ $permiso->modulo }}</td>
                                <td class="px-3 py-4 font-medium text-slate-900">{{ $permiso->nombre }}</td>
                                <td class="px-3 py-4"><code class="rounded bg-slate-100 px-2 py-1 text-xs">{{ $permiso->codigo }}</code></td>
                                <td class="px-3 py-4 text-slate-600">{{ $permiso->roles_count }}</td>
                                <td class="px-3 py-4 text-slate-600">{{ $permiso->estado ? 'Activo' : 'Inactivo' }}</td>
                                <td class="px-3 py-4">
                                    <div class="flex justify-end gap-2">
                                        @if (auth()->user()->hasPermission('permisos.editar'))
                                            <a href="{{ route('permisos.edit', $permiso) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700">Editar</a>
                                        @endif
                                        @if (auth()->user()->hasPermission('permisos.estado'))
                                            <form method="POST" action="{{ route('permisos.toggle-status', $permiso) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700">{{ $permiso->estado ? 'Desactivar' : 'Activar' }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $permisos->links() }}</div>
        </x-ui.card>
    </div>
</x-layouts.app>
