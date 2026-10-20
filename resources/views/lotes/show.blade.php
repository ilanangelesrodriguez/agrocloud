<x-layouts.app title="Detalle del lote">

    <div class="space-y-6">
        <a wire:navigate href="{{ route('incidencias.index', ['lote' => $lote->id_lote]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 dark:text-brand-300"><x-ui.icon name="clipboard-list" class="h-4 w-4" />Gestionar incidencias del lote</a>
        @if(auth()->user()->hasPermission('trazabilidad.ver'))<a wire:navigate href="{{ route('trazabilidad.show', $lote->id_lote) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700"><x-ui.icon name="clipboard-list" class="h-4 w-4" />Ver expediente e historial de trazabilidad</a>@endif
        @if($lote->estado === \App\Enums\LoteEstado::RETENIDO)<div role="status" class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">Este lote está retenido por una incidencia alta o crítica. Producción debe esperar al cierre del supervisor. Abre las incidencias del lote para ver el responsable y el siguiente paso.</div>@endif
        @if(!$lote->recepcion && auth()->user()->hasPermission('recepciones.crear'))<a wire:navigate href="{{ route('recepciones.index') }}" class="inline-flex rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white">Confirmar ingreso físico del lote</a>@endif
        {{-- AGROCLOUD INSPECTOR: acceso directo al muestreo --}}
        @if(auth()->user()->hasPermission('muestreos.crear') && $lote->recepcion()->exists() && $lote->estado->value === 'RECIBIDO')
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-900 dark:bg-brand-900/25">
                <div><p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Control de calidad</p><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Crea una muestra sobre este lote y continúa con la inspección.</p></div>
                <a wire:navigate href="{{ route('muestreos.create', ['lote' => $lote->id_lote]) }}" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">+ Crear muestreo</a>
            </div>
        @endif

        {{-- Encabezado --}}
        <div class="flex flex-col gap-3">

            <div>
                <a wire:navigate
                    href="{{ route('lotes.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 transition hover:text-brand-700 dark:hover:text-brand-300"
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
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        Detalle del lote
                    </h1>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Consulta la información completa del lote registrado.
                    </p>
                </div>

                @if (auth()->user()->hasPermission('lotes.editar') && !$lote->recepcion()->exists() && !$lote->muestreos()->exists())

                    <a wire:navigate
                        href="{{ route('lotes.edit', $lote->id_lote) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200 dark:focus:ring-brand-900/50"
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

        {{-- Información principal --}}
        <x-ui.card
            title="Información del lote"
            description="Datos registrados durante la recepción del lote."
        >

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- Código --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Código del lote
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                        {{ $lote->codigo_lote }}
                    </p>

                </div>

                {{-- Estado --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Estado
                    </p>

                    @php
                        $estado = $lote->estado instanceof \BackedEnum
                            ? $lote->estado->value
                            : $lote->estado;
                    @endphp

                    <div class="mt-1">

                        <x-ui.state-badge :state="$estado" />

                    </div>

                </div>

                {{-- Producto --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Producto
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                        {{ $lote->producto }}
                    </p>

                </div>

                {{-- Variedad --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Variedad
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                        {{ $lote->variedad ?: 'No especificada' }}
                    </p>

                </div>

                {{-- Peso --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Peso declarado
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                        {{ number_format((float) $lote->peso_kg, 2) }}
                        kg
                    </p>

                </div>

                <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peso recibido físicamente</p><p class="mt-1 text-sm dark:text-slate-200">{{ $lote->recepcion ? number_format((float) $lote->recepcion->peso_recibido_kg, 2).' kg' : 'Pendiente de confirmación de ingreso' }}</p></div>
                {{-- Fecha de recepción --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ $lote->recepcion ? 'Fecha de recepción física' : 'Fecha prevista de recepción' }}
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
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

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Nombre / razón social
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ $lote->productor->nombre_razon_social }}
                        </p>

                    </div>

                    {{-- RUC --}}
                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            RUC
                        </p>

                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            {{ $lote->productor->ruc }}
                        </p>

                    </div>

                    {{-- Fundo --}}
                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Fundo
                        </p>

                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            {{ $lote->productor->fundo ?: 'No especificado' }}
                        </p>

                    </div>

                    {{-- Ubicación --}}
                    <div>

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            Ubicación
                        </p>

                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                            {{ $lote->productor->ubicacion ?: 'No especificada' }}
                        </p>

                    </div>

                </div>

                <div class="mt-6 border-t border-slate-100 dark:border-slate-700 pt-5">

                    @if (auth()->user()->hasPermission('productores.ver'))

                        <a wire:navigate
                            href="{{ route('productores.show', $lote->productor->id_productor) }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 dark:text-brand-300 transition hover:text-brand-800 dark:hover:text-brand-200"
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

                <div class="flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-slate-800 p-4">

                    <x-ui.icon
                        name="user"
                        class="h-5 w-5 text-slate-400"
                    />

                    <p class="text-sm text-slate-500 dark:text-slate-400">
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

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        ID del lote
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                        {{ $lote->id_lote }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Empresa
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                        {{ $lote->empresa?->nombre_comercial ?? 'Empresa actual' }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Registrado
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                        {{ $lote->created_at?->format('d/m/Y H:i') }}
                    </p>

                </div>

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Última actualización
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                        {{ $lote->updated_at?->format('d/m/Y H:i') }}
                    </p>

                </div>

            </div>

        </x-ui.card>

        {{-- Acciones --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

            <a wire:navigate
                href="{{ route('lotes.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-100 dark:focus:ring-slate-700"
            >
                Regresar a lotes
            </a>

            @if (auth()->user()->hasPermission('lotes.editar') && !$lote->recepcion()->exists() && !$lote->muestreos()->exists())

                <a wire:navigate
                    href="{{ route('lotes.edit', $lote->id_lote) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200 dark:focus:ring-brand-900/50"
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
