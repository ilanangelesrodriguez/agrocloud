<x-layouts.app title="Detalle del lote">

    <div class="space-y-6">

        {{-- Encabezado --}}
        <div class="flex flex-col gap-3">

            <div>
                <a
                    href="{{ route('lotes.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-brand-700"
                >
                    <x-ui.icon
                        name="arrow-left"
                        class="h-4 w-4"
                    />

                    Regresar a lotes
                </a>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">
                        Detalle del lote
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Consulta la información completa del lote registrado.
                    </p>
                </div>

                @if (auth()->user()->hasPermission('lotes.editar'))

                    <a
                        href="{{ route('lotes.edit', $lote->id_lote) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200"
                    >
                        <x-ui.icon
                            name="pencil"
                            class="h-4 w-4"
                        />

                        Editar lote
                    </a>

                @endif

            </div>

        </div>

        {{-- Mensaje de éxito --}}
        @if (session('success'))

            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>

        @endif

        {{-- Información principal --}}
        <x-ui.card
            title="Información del lote"
            description="Datos registrados durante la recepción del lote."
        >

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- Código --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Código del lote
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900">
                        {{ $lote->codigo_lote }}
                    </p>

                </div>

                {{-- Estado --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Estado
                    </p>

                    @php
                        $estado = $lote->estado instanceof \BackedEnum
                            ? $lote->estado->value
                            : $lote->estado;
                    @endphp

                    <div class="mt-1">

                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            {{ $estado }}
                        </span>

                    </div>

                </div>

                {{-- Producto --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Producto
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900">
                        {{ $lote->producto }}
                    </p>

                </div>

                {{-- Variedad --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Variedad
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $lote->variedad ?: 'No especificada' }}
                    </p>

                </div>

                {{-- Peso --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Peso recibido
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900">
                        {{ number_format((float) $lote->peso_kg, 2) }}
                        kg
                    </p>

                </div>

                {{-- Fecha de recepción --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Fecha de recepción
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $lote->fecha_recepcion?->format('d/m/Y H:i') }}
                    </p>

                </div>

            </div>

        </x-ui.card>

        {{-- Productor --}}
        <x-ui.card
            title="Productor asociado"
            description="Información del productor responsable del lote."
        >

            @if ($lote->productor)

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    {{-- Nombre --}}
                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Nombre / razón social
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $lote->productor->nombre_razon_social }}
                        </p>

                    </div>

                    {{-- RUC --}}
                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            RUC
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ $lote->productor->ruc }}
                        </p>

                    </div>

                    {{-- Fundo --}}
                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Fundo
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ $lote->productor->fundo ?: 'No especificado' }}
                        </p>

                    </div>

                    {{-- Ubicación --}}
                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Ubicación
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ $lote->productor->ubicacion ?: 'No especificada' }}
                        </p>

                    </div>

                </div>

                <div class="mt-6 border-t border-slate-100 pt-5">

                    @if (auth()->user()->hasPermission('productores.ver'))

                        <a
                            href="{{ route('productores.show', $lote->productor->id_productor) }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 transition hover:text-brand-800"
                        >
                            <x-ui.icon
                                name="eye"
                                class="h-4 w-4"
                            />

                            Ver detalle del productor
                        </a>

                    @endif

                </div>

            @else

                <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4">

                    <x-ui.icon
                        name="user"
                        class="h-5 w-5 text-slate-400"
                    />

                    <p class="text-sm text-slate-500">
                        Este lote no tiene un productor asociado.
                    </p>

                </div>

            @endif

        </x-ui.card>

        {{-- Información del registro --}}
        <x-ui.card
            title="Información del registro"
            description="Datos técnicos del registro del lote."
        >

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        ID del lote
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $lote->id_lote }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Empresa
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $lote->empresa?->nombre_comercial ?? 'Empresa actual' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Registrado
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $lote->created_at?->format('d/m/Y H:i') }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Última actualización
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $lote->updated_at?->format('d/m/Y H:i') }}
                    </p>

                </div>

            </div>

        </x-ui.card>

        {{-- Acciones --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <a
                href="{{ route('lotes.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100"
            >
                Regresar a lotes
            </a>

            @if (auth()->user()->hasPermission('lotes.editar'))

                <a
                    href="{{ route('lotes.edit', $lote->id_lote) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200"
                >
                    <x-ui.icon
                        name="pencil"
                        class="h-4 w-4"
                    />

                    Editar lote
                </a>

            @endif

        </div>

    </div>

</x-layouts.app>
