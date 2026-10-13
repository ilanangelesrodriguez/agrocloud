<x-layouts.app title="Lotes del productor">

    <div class="space-y-6">

        {{-- Regresar --}}
        <div>
            <a wire:navigate
                href="{{ route('productores.show', $productorModel->id_productor) }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-lg px-1 py-1 text-sm font-semibold text-slate-600 dark:text-slate-300 transition hover:text-brand-700 dark:hover:text-brand-300"
            >
                <x-ui.icon
                    name="arrow-left"
                    class="h-4 w-4"
                />

                Regresar al productor
            </a>
        </div>

        {{-- Encabezado --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                    Lotes del productor
                </h1>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $productorModel->nombre_razon_social }}
                </p>

            </div>

            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3">

                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    RUC
                </p>

                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                    {{ $productorModel->ruc }}
                </p>

            </div>

        </div>

        {{-- Información del productor --}}
        <x-ui.card>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Productor
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                        {{ $productorModel->nombre_razon_social }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Fundo
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                        {{ $productorModel->fundo ?: 'No registrado' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Ubicación
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                        {{ implode(' · ', array_filter([$productorModel->distrito, $productorModel->provincia, $productorModel->departamento])) ?: 'Ubicación por completar' }}
                    </p>

                </div>

            </div>

        </x-ui.card>

        {{-- Listado de lotes --}}
        <x-ui.card>

            <div class="mb-5">

                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Lotes registrados
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
                                Producto
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Variedad
                            </th>

                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Peso
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Fecha recepción
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Estado
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 bg-white dark:bg-slate-900">

                        @forelse ($lotes as $lote)

                            <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-700">

                                {{-- Código --}}
                                <td class="whitespace-nowrap px-4 py-4">

                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $lote->codigo_lote }}
                                    </span>

                                </td>

                                {{-- Producto --}}
                                <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-200">
                                    {{ $lote->producto }}
                                </td>

                                {{-- Variedad --}}
                                <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $lote->variedad ?: '—' }}
                                </td>

                                {{-- Peso --}}
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-slate-700 dark:text-slate-200">
                                    {{ number_format((float) $lote->peso_kg, 2, ',', '.') }}
                                    kg
                                </td>

                                {{-- Fecha --}}
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600 dark:text-slate-300">

                                    @if ($lote->fecha_recepcion)
                                        {{ $lote->fecha_recepcion->format('d/m/Y H:i') }}
                                    @else
                                        —
                                    @endif

                                </td>

                                {{-- Estado --}}
                                <td class="px-4 py-4">

                                    @php
                                        $estado = $lote->estado?->value ?? $lote->estado;
                                    @endphp

                                    @if ($estado)
                                        <x-ui.state-badge :state="$estado" />
                                    @else
                                        <span class="text-sm text-slate-500 dark:text-slate-400">
                                            —
                                        </span>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
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
                                            Este productor todavía no tiene lotes asociados.
                                        </p>

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
