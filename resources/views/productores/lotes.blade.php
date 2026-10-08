<x-layouts.app title="Lotes del productor">

    <div class="space-y-6">

        {{-- Regresar --}}
        <div>
            <a
                href="{{ route('productores.show', $productorModel->id_productor) }}"
                class="inline-flex items-center gap-2 rounded-lg px-1 py-1 text-sm font-semibold text-slate-600 transition hover:text-brand-700"
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

                <h1 class="text-2xl font-semibold text-slate-900">
                    Lotes del productor
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $productorModel->nombre_razon_social }}
                </p>

            </div>

            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">

                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    RUC
                </p>

                <p class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $productorModel->ruc }}
                </p>

            </div>

        </div>

        {{-- Información del productor --}}
        <x-ui.card>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Productor
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900">
                        {{ $productorModel->nombre_razon_social }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Fundo
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $productorModel->fundo ?: 'No registrado' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Ubicación
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $productorModel->ubicacion ?: 'No registrada' }}
                    </p>

                </div>

            </div>

        </x-ui.card>

        {{-- Listado de lotes --}}
        <x-ui.card>

            <div class="mb-5">

                <h2 class="text-lg font-semibold text-slate-900">
                    Lotes registrados
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $lotes->total() }}
                    {{ $lotes->total() === 1 ? 'lote registrado' : 'lotes registrados' }}
                </p>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead>

                        <tr class="text-left">

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Código
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Producto
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Variedad
                            </th>

                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Peso
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Fecha recepción
                            </th>

                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Estado
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse ($lotes as $lote)

                            <tr class="transition hover:bg-slate-50">

                                {{-- Código --}}
                                <td class="whitespace-nowrap px-4 py-4">

                                    <span class="text-sm font-semibold text-slate-900">
                                        {{ $lote->codigo_lote }}
                                    </span>

                                </td>

                                {{-- Producto --}}
                                <td class="px-4 py-4 text-sm text-slate-700">
                                    {{ $lote->producto }}
                                </td>

                                {{-- Variedad --}}
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $lote->variedad ?: '—' }}
                                </td>

                                {{-- Peso --}}
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-slate-700">
                                    {{ number_format((float) $lote->peso_kg, 2, ',', '.') }}
                                    kg
                                </td>

                                {{-- Fecha --}}
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">

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
                                        <x-ui.status-badge
                                            :active="$estado === 'RECIBIDO'"
                                            :label="$estado"
                                        />
                                    @else
                                        <span class="text-sm text-slate-500">
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

                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">

                                            <x-ui.icon
                                                name="briefcase"
                                                class="h-6 w-6 text-slate-400"
                                            />

                                        </div>

                                        <h3 class="mt-4 text-sm font-semibold text-slate-900">
                                            No hay lotes registrados
                                        </h3>

                                        <p class="mt-1 max-w-md text-sm text-slate-500">
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

                <div class="mt-5 border-t border-slate-100 pt-5">

                    {{ $lotes->links() }}

                </div>

            @endif

        </x-ui.card>

    </div>

</x-layouts.app>
