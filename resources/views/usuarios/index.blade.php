    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-brand-700 dark:text-brand-300">Administración de empresa</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950 dark:text-slate-100">Usuarios de {{ auth()->user()->empresa?->nombre_comercial }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Administra los usuarios, roles y accesos de tu empresa.</p>
            </div>

            @if (auth()->user()->hasPermission('usuarios.crear'))
                <a href="{{ route('usuarios.create') }}" wire:navigate class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                    <x-ui.icon name="plus" class="h-4 w-4" /> Nuevo usuario
                </a>
            @endif
        </div>

        <form wire:submit="buscarUsuarios" class="flex flex-col gap-3 sm:flex-row sm:items-start">
            <div data-search-field class="w-full sm:max-w-lg">
                <label for="buscar-usuario" class="sr-only">Buscar usuarios por nombre, correo o cargo</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-0 flex h-11 items-center text-slate-400"><x-ui.icon name="search" class="h-4 w-4" /></span>
                    <input id="buscar-usuario" data-search-input type="search" wire:model="buscar" placeholder="Nombre, correo o cargo" class="h-11 w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 pl-10 pr-3.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50">
                </div>
            </div>
            <div class="flex shrink-0 gap-2">
                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="buscarUsuarios" class="h-11 w-full sm:w-auto">
                    <span wire:loading.remove wire:target="buscarUsuarios">Buscar</span>
                    <span wire:loading wire:target="buscarUsuarios">Buscando…</span>
                </x-ui.button>
                @if ($filtro !== '')
                    <x-ui.button type="button" variant="secondary" wire:click="limpiarBusqueda" class="h-11">Limpiar</x-ui.button>
                @endif
            </div>
        </form>

        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        <tr><th class="px-3 py-3">Usuario</th><th class="px-3 py-3">Rol</th><th class="px-3 py-3">Cargo</th><th class="px-3 py-3">Estado</th><th class="px-3 py-3 text-right">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($usuarios as $usuario)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/60">
                                <td class="px-3 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($usuario->foto_perfil_url)
                                            <img src="{{ $usuario->foto_perfil_url }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <div class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300">{{ strtoupper(substr($usuario->nombre, 0, 1)) }}{{ strtoupper(substr($usuario->apellido ?? '', 0, 1)) }}</div>
                                        @endif
                                        <div><p class="font-medium text-slate-900 dark:text-slate-100">{{ $usuario->nombre_completo }}</p><p class="text-xs text-slate-500 dark:text-slate-400">{{ $usuario->correo }}</p></div>
                                    </div>
                                </td>
                                <td class="px-3 py-4 text-slate-600 dark:text-slate-300">{{ $usuario->rol?->nombre ?? 'Sin rol' }}</td>
                                <td class="px-3 py-4 text-slate-600 dark:text-slate-300">{{ $usuario->cargo ?: '—' }}</td>
                                <td class="px-3 py-4"><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-medium','bg-emerald-50 dark:bg-emerald-950/35 text-emerald-700 dark:text-emerald-300' => $usuario->estado,'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' => ! $usuario->estado])>{{ $usuario->estado ? 'Activo' : 'Inactivo' }}</span></td>
                                <td class="px-3 py-4">
                                    <div class="flex justify-end gap-2">
                                        @if (auth()->user()->hasPermission('usuarios.editar'))
                                            <a href="{{ route('usuarios.edit', $usuario) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 transition hover:border-brand-200 dark:hover:border-brand-800 hover:bg-brand-50 dark:hover:bg-brand-900/35 hover:text-brand-700 dark:hover:text-brand-300"><x-ui.icon name="pencil" class="h-3.5 w-3.5" />Editar</a>
                                        @endif
                                        @if (auth()->user()->hasPermission('usuarios.estado') && ! $usuario->is(auth()->user()))
                                            <form method="POST" action="{{ route('usuarios.toggle-status', $usuario) }}" data-ajax-form>@csrf @method('PATCH')<button type="submit" class="rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">{{ $usuario->estado ? 'Desactivar' : 'Activar' }}</button></form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-10 text-center text-slate-500 dark:text-slate-400">No se encontraron usuarios.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $usuarios->links() }}</div>
        </x-ui.card>
    </div>
