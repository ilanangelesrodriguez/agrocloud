<x-layouts.app title="Usuarios">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-950">Usuarios de la empresa</h2>
                <p class="mt-1 text-sm text-slate-500">Administra cuentas, roles, estado y foto de perfil.</p>
            </div>

            @if (auth()->user()->hasPermission('usuarios.crear'))
                <a href="{{ route('usuarios.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">
                    Nuevo usuario
                </a>
            @endif
        </div>

        <form method="GET" class="flex gap-2">
            <input type="search" name="q" value="{{ $search }}" placeholder="Buscar por nombre o correo" class="w-full max-w-lg rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            <x-ui.button type="submit" variant="secondary">Buscar</x-ui.button>
        </form>

        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Usuario</th>
                            <th class="px-3 py-3">Rol</th>
                            <th class="px-3 py-3">Cargo</th>
                            <th class="px-3 py-3">Estado</th>
                            <th class="px-3 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($usuarios as $usuario)
                            <tr>
                                <td class="px-3 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($usuario->foto_perfil_url)
                                            <img src="{{ $usuario->foto_perfil_url }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <div class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                                                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}{{ strtoupper(substr($usuario->apellido ?? '', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $usuario->nombre_completo }}</p>
                                            <p class="text-xs text-slate-500">{{ $usuario->correo }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-4 text-slate-600">{{ $usuario->rol?->nombre ?? 'Sin rol' }}</td>
                                <td class="px-3 py-4 text-slate-600">{{ $usuario->cargo ?: '—' }}</td>
                                <td class="px-3 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                        'bg-emerald-50 text-emerald-700' => $usuario->estado,
                                        'bg-slate-100 text-slate-600' => ! $usuario->estado,
                                    ])>{{ $usuario->estado ? 'Activo' : 'Inactivo' }}</span>
                                </td>
                                <td class="px-3 py-4">
                                    <div class="flex justify-end gap-2">
                                        @if (auth()->user()->hasPermission('usuarios.editar'))
                                            <a href="{{ route('usuarios.edit', $usuario) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Editar</a>
                                        @endif
                                        @if (auth()->user()->hasPermission('usuarios.estado') && ! $usuario->is(auth()->user()))
                                            <form method="POST" action="{{ route('usuarios.toggle-status', $usuario) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                                    {{ $usuario->estado ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-10 text-center text-slate-500">No se encontraron usuarios.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $usuarios->links() }}</div>
        </x-ui.card>
    </div>
</x-layouts.app>
