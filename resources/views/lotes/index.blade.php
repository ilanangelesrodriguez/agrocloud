<x-layouts.app title="Lotes">

    <div class="space-y-6">

        {{-- Encabezado --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                    Lotes
                </h1>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Consulta y administra los lotes registrados en tu empresa.
                </p>
            </div>

            @if (auth()->user()->hasPermission('lotes.crear'))
                <a wire:navigate
                    href="{{ route('lotes.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200 dark:focus:ring-brand-900/50"
                >
                    <x-ui.icon name="plus" class="h-4 w-4" />
                    <span>Nuevo lote</span>
                </a>
            @endif

        </div>

        {{-- Mensaje de éxito --}}

        {{-- Mensaje de error --}}

        {{-- Buscador --}}
        <x-ui.card>

            <form data-ajax-form
                method="GET"
                action="{{ route('lotes.index') }}"
                class="flex flex-col gap-4 sm:flex-row sm:items-end"
            >

                <div class="flex-1">

                    <label
                        for="buscar"
                        class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                    >
                        Buscar lote
                    </label>

                    <div class="relative">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <x-ui.icon
                                name="search"
                                class="h-4 w-4 text-slate-400"
                            />
                        </div>

                        <input data-search-input
                            id="buscar"
                            name="buscar"
                            type="text"
                            value="{{ request('buscar') }}"
                            placeholder="Código, producto, variedad, productor o RUC..."
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 py-2.5 pl-10 pr-3.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50"
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
                        <a wire:navigate
                            href="{{ route('lotes.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-100 dark:focus:ring-slate-700"
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

                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Listado de lotes
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $lotes->total() }}
                    {{ $lotes->total() === 1 ? 'lote registrado' : 'lotes registrados' }}
                </p>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">

                    <thead>

                        <tr class="text-left">

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Código
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Productor
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Producto
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Variedad
                            </th>

                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Peso
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Recepción
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Estado
                            </th>

                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Acciones
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 bg-white dark:bg-slate-900">

                        @forelse ($lotes as $lote)

                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-700">

                                {{-- Código --}}
                                <td class="whitespace-nowrap px-4 py-4">

                                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $lote->codigo_lote }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        ID: {{ $lote->id_lote }}
                                    </div>

                                </td>

                                {{-- Productor --}}
                                <td class="px-4 py-4">

                                    @if ($lote->productor)

                                        <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $lote->productor->nombre_razon_social }}
                                        </div>

                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            RUC: {{ $lote->productor->ruc }}
                                        </div>

                                    @else

                                        <span class="text-sm text-slate-400">
                                            Sin productor
                                        </span>

                                    @endif

                                </td>

                                {{-- Producto --}}
                                <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $lote->producto }}
                                </td>

                                {{-- Variedad --}}
                                <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $lote->variedad ?: '—' }}
                                </td>

                                {{-- Peso --}}
                                <td class="whitespace-nowrap px-4 py-4 text-right">

                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        {{ number_format((float) $lote->peso_kg, 2) }}
                                    </span>

                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        kg
                                    </span>

                                </td>

                                {{-- Fecha de recepción --}}
                                <td class="whitespace-nowrap px-4 py-4">

                                    <div class="text-sm text-slate-700 dark:text-slate-200">
                                        {{ $lote->fecha_recepcion?->format('d/m/Y') }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $lote->fecha_recepcion?->format('H:i') }}
                                    </div>

                                </td>

                                {{-- Estado --}}
                                <td class="whitespace-nowrap px-4 py-4">

                                    @php
                                        $estado = $lote->estado instanceof \BackedEnum
                                            ? $lote->estado->value
                                            : $lote->estado;
                                    @endphp

                                    <x-ui.state-badge :state="$estado" />

                                </td>

                                {{-- Acciones --}}
                                <td class="whitespace-nowrap px-4 py-4">

                                    <div class="flex justify-end gap-2">

                                        @if (auth()->user()->hasPermission('lotes.ver'))

                                            <a wire:navigate
                                                href="{{ route('lotes.show', $lote->id_lote) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-brand-700 dark:hover:text-brand-300"
                                            >
                                                <x-ui.icon
                                                    name="eye"
                                                    class="h-4 w-4"
                                                />
                                                Ver
                                            </a>

                                        @endif

                                        @if (auth()->user()->hasPermission('lotes.editar'))

                                            <a wire:navigate
                                                href="{{ route('lotes.edit', $lote->id_lote) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-brand-700 dark:hover:text-brand-300"
                                            >
                                                <x-ui.icon
                                                    name="pencil"
                                                    class="h-4 w-4"
                                                />
                                                Editar
                                            </a>

                                        @endif
                                        @if (auth()->user()->hasPermission('lotes.eliminar'))
                                            <form
                                                method="POST"
                                                action="{{ route('lotes.destroy', $lote->id_lote) }}"
                                                onsubmit="return confirm('¿Estás seguro de que deseas anular el lote {{ $lote->codigo_lote }}? Se conservará su historial.');"
                                                class="inline"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 dark:border-red-900/50 bg-white dark:bg-slate-900 p-2 text-red-600 dark:text-red-400 transition hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 focus:outline-none focus:ring-4 focus:ring-red-100 dark:focus:ring-red-900/30"
                                                    title="Anular lote"
                                                    aria-label="Anular lote {{ $lote->codigo_lote }}"
                                                >
                                                    <x-ui.icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="px-4 py-12 text-center"
                                >

                                    <div class="flex flex-col items-center">

                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">

                                            <x-ui.icon
                                                name="briefcase"
                                                class="h-6 w-6 text-slate-400"
                                            />

                                        </div>

                                        <h3 class="mt-4 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            No hay lotes registrados
                                        </h3>

                                        <p class="mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400">

                                            @if (request()->filled('buscar'))

                                                No encontramos lotes que coincidan con
                                                "{{ request('buscar') }}".

                                            @else

                                                Todavía no existen lotes registrados
                                                en tu empresa.

                                            @endif

                                        </p>

                                        @if (
                                            !request()->filled('buscar') &&
                                            auth()->user()->hasPermission('lotes.crear')
                                        )

                                            <a wire:navigate
                                                href="{{ route('lotes.create') }}"
                                                class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
                                            >

                                                <x-ui.icon
                                                    name="plus"
                                                    class="h-4 w-4"
                                                />

                                                Nuevo lote

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
            @if ($lotes->hasPages())

                <div class="mt-5 border-t border-slate-100 dark:border-slate-700 pt-5">

                    {{ $lotes->links() }}

                </div>

            @endif

        </x-ui.card>

    </div>

</x-layouts.app>
