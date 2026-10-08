<x-layouts.app title="Detalle de productor">

    <div class="space-y-6">

        {{-- Navegación --}}
        <div>
            <a
                href="{{ route('productores.index') }}"
                class="inline-flex items-center gap-2 rounded-lg px-1 py-1 text-sm font-semibold text-slate-600 dark:text-slate-300 transition hover:text-brand-700 dark:hover:text-brand-300"
            >
                <x-ui.icon
                    name="arrow-left"
                    class="h-4 w-4"
                />

                Regresar a productores
            </a>
        </div>

        {{-- Encabezado --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            <div>

                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                    {{ $productorModel->nombre_razon_social }}
                </h1>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Detalle del productor registrado en tu empresa.
                </p>

            </div>

            <div class="flex flex-wrap gap-2">

                {{-- Ver lotes --}}
                @if (auth()->user()->hasPermission('productores.lotes.ver'))

                    <a
                        href="{{ route('productores.lotes', $productorModel->id_productor) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-100 dark:focus:ring-slate-700"
                    >

                        <x-ui.icon
                            name="briefcase"
                            class="h-4 w-4"
                        />

                        Ver lotes

                    </a>

                @endif

                {{-- Editar --}}
                @if (auth()->user()->hasPermission('productores.editar'))

                    <a
                        href="{{ route('productores.edit', $productorModel->id_productor) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200 dark:focus:ring-brand-900/50"
                    >

                        <x-ui.icon
                            name="pencil"
                            class="h-4 w-4"
                        />

                        Editar

                    </a>

                @endif

            </div>

        </div>

        {{-- Información principal --}}
        <x-ui.card
            title="Información del productor"
            description="Datos registrados del productor."
        >

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                {{-- RUC --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        RUC
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                        {{ $productorModel->ruc }}
                    </p>

                </div>

                {{-- Nombre / razón social --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Nombre / razón social
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                        {{ $productorModel->nombre_razon_social }}
                    </p>

                </div>

                {{-- Fundo --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Fundo
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                        {{ $productorModel->fundo ?: 'No registrado' }}
                    </p>

                </div>

                {{-- Ubicación --}}
                <div>

                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Ubicación
                    </p>

                    <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                        {{ $productorModel->ubicacion ?: 'No registrada' }}
                    </p>

                </div>

            </div>

        </x-ui.card>

        {{-- Resumen --}}
        <x-ui.card
            title="Resumen"
            description="Información relacionada con el productor."
        >

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                {{-- Total de lotes --}}
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-5">

                    <div class="flex items-center justify-between">

                        <div>

                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                                Lotes registrados
                            </p>

                            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">
                                {{ $productorModel->lotes_count }}
                            </p>

                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white dark:bg-slate-900 shadow-sm">

                            <x-ui.icon
                                name="briefcase"
                                class="h-5 w-5 text-brand-600 dark:text-brand-400"
                            />

                        </div>

                    </div>

                    @if (auth()->user()->hasPermission('productores.lotes.ver'))

                        <a
                            href="{{ route('productores.lotes', $productorModel->id_productor) }}"
                            class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 dark:text-brand-300 transition hover:text-brand-800 dark:hover:text-brand-200"
                        >

                            Ver lotes

                            <x-ui.icon
                                name="chevron-right"
                                class="h-4 w-4"
                            />

                        </a>

                    @endif

                </div>

                {{-- Identificador --}}
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-5">

                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        Identificador
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        #{{ $productorModel->id_productor }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        ID interno del productor.
                    </p>

                </div>

            </div>

        </x-ui.card>

    </div>

</x-layouts.app>
