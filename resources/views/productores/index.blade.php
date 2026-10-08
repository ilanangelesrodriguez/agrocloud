<x-layouts.app title="Productores">

    <div class="space-y-6">

        {{-- Encabezado --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-semibold text-slate-900">
                    Productores
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Consulta y administra los productores registrados en tu empresa.
                </p>
            </div>

            @if (auth()->user()->hasPermission('productores.crear'))
                <a
                    href="{{ route('productores.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200"
                >
                    <x-ui.icon name="plus" class="h-4 w-4" />
                    <span>Nuevo productor</span>
                </a>
            @endif

        </div>

       {{-- Mensaje de éxito --}}
       @if (session('success'))
           <x-ui.alert type="success">
               {{ session('success') }}
           </x-ui.alert>
       @endif

       {{-- Mensaje de error --}}
       @if (session('error'))
           <x-ui.alert type="error">
               {{ session('error') }}
           </x-ui.alert>
       @endif

        {{-- Buscador --}}
        <x-ui.card>

            <form
                method="GET"
                action="{{ route('productores.index') }}"
                class="flex flex-col gap-4 sm:flex-row sm:items-end"
            >

                <div class="flex-1">

                    <label
                        for="buscar"
                        class="mb-1.5 block text-sm font-semibold text-slate-700"
                    >
                        Buscar productor
                    </label>

                    <div class="relative">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <x-ui.icon
                                name="search"
                                class="h-4 w-4 text-slate-400"
                            />
                        </div>

                        <input
                            id="buscar"
                            name="buscar"
                            type="text"
                            value="{{ request('buscar') }}"
                            placeholder="RUC, nombre, fundo o ubicación..."
                            class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
                        >

                    </div>

                </div>

                <div class="flex gap-2">

                    <x-ui.button type="submit">
                        <x-ui.icon
                            name="search"
                            class="mr-2 h-4 w-4"
                        />
                        Buscar
                    </x-ui.button>

                    @if (request()->filled('buscar'))
                        <a
                            href="{{ route('productores.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100"
                        >
                            Limpiar
                        </a>
                    @endif

                </div>

            </form>

        </x-ui.card>

        {{-- Listado --}}
        <x-ui.card>

            <div class="mb-5">

                <h2 class="text-lg font-semibold text-slate-900">
                    Listado de productores
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $productores->total() }}
                    {{ $productores->total() === 1 ? 'productor registrado' : 'productores registrados' }}
                </p>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead>

                        <tr class="text-left">

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                RUC
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Productor
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Fundo
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Ubicación
                            </th>

                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Acciones
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse ($productores as $productor)

                            <tr class="transition hover:bg-slate-50">

                                {{-- RUC --}}
                                <td class="whitespace-nowrap px-4 py-4">

                                    <span class="text-sm font-semibold text-slate-900">
                                        {{ $productor->ruc }}
                                    </span>

                                </td>

                                {{-- Productor --}}
                                <td class="px-4 py-4">

                                    <div class="text-sm font-semibold text-slate-900">
                                        {{ $productor->nombre_razon_social }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        ID: {{ $productor->id_productor }}
                                    </div>

                                </td>

                                {{-- Fundo --}}
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $productor->fundo ?: '—' }}
                                </td>

                                {{-- Ubicación --}}
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $productor->ubicacion ?: '—' }}
                                </td>

                                {{-- Acciones --}}
                                <td class="whitespace-nowrap px-4 py-4">

                                    <div class="flex justify-end gap-2">

                                        @if (auth()->user()->hasPermission('productores.ver'))

                                            <a
                                                href="{{ route('productores.show', $productor->id_productor) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 hover:text-brand-700"
                                            >
                                                <x-ui.icon
                                                    name="eye"
                                                    class="h-4 w-4"
                                                />
                                                Ver
                                            </a>

                                        @endif

                                        @if (auth()->user()->hasPermission('productores.editar'))

                                            <a
                                                href="{{ route('productores.edit', $productor->id_productor) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 hover:text-brand-700"
                                            >
                                                <x-ui.icon
                                                    name="pencil"
                                                    class="h-4 w-4"
                                                />
                                                Editar
                                            </a>

                                        @endif

                                        @if (auth()->user()->hasPermission('productores.eliminar'))

                                            <form
                                                method="POST"
                                                action="{{ route('productores.destroy', $productor->id_productor) }}"
                                                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este productor?');"
                                                class="inline"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white p-2 text-red-600 transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-4 focus:ring-red-100"
                                                    title="Eliminar productor"
                                                    aria-label="Eliminar productor"
                                                >
                                                    <x-ui.icon
                                                        name="trash"
                                                        class="h-4 w-4"
                                                    />
                                                </button>
                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="px-4 py-12 text-center"
                                >

                                    <div class="flex flex-col items-center">

                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">

                                            <x-ui.icon
                                                name="users"
                                                class="h-6 w-6 text-slate-400"
                                            />

                                        </div>

                                        <h3 class="mt-4 text-sm font-semibold text-slate-900">
                                            No hay productores registrados
                                        </h3>

                                        <p class="mt-1 max-w-md text-sm text-slate-500">

                                            @if (request()->filled('buscar'))

                                                No encontramos productores que coincidan con
                                                "{{ request('buscar') }}".

                                            @else

                                                Todavía no existen productores registrados
                                                en tu empresa.

                                            @endif

                                        </p>

                                        @if (
                                            !request()->filled('buscar') &&
                                            auth()->user()->hasPermission('productores.crear')
                                        )

                                            <a
                                                href="{{ route('productores.create') }}"
                                                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
                                            >

                                                <x-ui.icon
                                                    name="plus"
                                                    class="h-4 w-4"
                                                />

                                                Nuevo productor

                                            </a>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Paginación --}}
            @if ($productores->hasPages())

                <div class="mt-5 border-t border-slate-100 pt-5">

                    {{ $productores->links() }}

                </div>

            @endif

        </x-ui.card>

    </div>

</x-layouts.app>
