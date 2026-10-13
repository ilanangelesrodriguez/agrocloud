<x-layouts.app title="Editar lote">

    <div class="space-y-6">

        {{-- Encabezado --}}
        <div class="flex flex-col gap-3">

            <div>
                <a wire:navigate
                    href="{{ route('lotes.show', $lote->id_lote) }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 transition hover:text-brand-700 dark:hover:text-brand-300"
                >
                    <x-ui.icon
                        name="arrow-left"
                        class="h-4 w-4"
                    />

                    Regresar al detalle
                </a>
            </div>

            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                    Editar lote
                </h1>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Actualiza la información del lote registrado.
                </p>
            </div>

        </div>

        {{-- Errores de validación --}}
        @if ($errors->any())

            <x-ui.alert type="error">

                <div class="font-semibold">
                    No se pudo actualizar el lote.
                </div>

                <ul class="mt-2 list-disc space-y-1 pl-5">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </x-ui.alert>

        @endif

        {{-- Formulario --}}
        <x-ui.card
            title="Información del lote"
            description="Modifica los datos que necesites actualizar."
        >

            <form data-ajax-form
                method="POST"
                action="{{ route('lotes.update', $lote->id_lote) }}"
                class="space-y-6"
            >

                @csrf
                @method('PUT')

                {{-- Datos principales --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- Productor --}}
                    <div>

                        <label
                            for="id_productor"
                            class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Productor
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="id_productor"
                            name="id_productor"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('id_productor') border-red-400 focus:border-red-500 focus:ring-red-100 dark:focus:ring-red-900/50 @enderror"
                        >

                            <option value="">
                                Selecciona un productor
                            </option>

                            @foreach ($productores as $productor)

                                <option
                                    value="{{ $productor->id_productor }}"
                                    @selected(
                                        old(
                                            'id_productor',
                                            $lote->id_productor
                                        ) == $productor->id_productor
                                    )
                                >
                                    {{ $productor->nombre_razon_social }}
                                    — RUC: {{ $productor->ruc }}
                                </option>

                            @endforeach

                        </select>

                        @error('id_productor')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-300">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Código del lote --}}
                    <div>

                        <label
                            for="codigo_lote"
                            class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Código del lote
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="codigo_lote"
                            name="codigo_lote"
                            type="text"
                            maxlength="50"
                            value="{{ old('codigo_lote', $lote->codigo_lote) }}"
                            placeholder="Ej. LOTE-2026-001"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('codigo_lote') border-red-400 focus:border-red-500 focus:ring-red-100 dark:focus:ring-red-900/50 @enderror"
                        >

                        @error('codigo_lote')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-300">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                            El código debe ser único dentro de tu empresa.
                        </p>

                    </div>

                    {{-- Producto --}}
                    <div>

                        <label
                            for="producto"
                            class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Producto
                            <span class="text-red-500">*</span>
                        </label>

                        <x-ui.master-select name="producto" kind="producto" :value="$lote->producto" required />

                        @error('producto')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-300">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Variedad --}}
                    <div>

                        <label
                            for="variedad"
                            class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Variedad
                        </label>

                        <x-ui.master-select name="variedad" kind="variedad" :value="$lote->variedad" />

                        @error('variedad')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-300">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Peso --}}
                    <div>

                        <label
                            for="peso_kg"
                            class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Peso recibido
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="peso_kg"
                            name="peso_kg"
                            type="number"
                            min="0"
                            step="0.01"
                            value="{{ old('peso_kg', (float) $lote->peso_kg / app(\App\Services\MaestroCatalog::class)->weightFactor($lote->unidad_medida)) }}"
                            placeholder="Ej. 1250.50"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('peso_kg') border-red-400 focus:border-red-500 focus:ring-red-100 dark:focus:ring-red-900/50 @enderror"
                        >

                        @error('peso_kg')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-300">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Fecha de recepción --}}
                    <div>

                        <label
                            for="fecha_recepcion"
                            class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Fecha y hora de recepción
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="fecha_recepcion"
                            name="fecha_recepcion"
                            type="datetime-local"
                            value="{{ old('fecha_recepcion', $lote->fecha_recepcion?->format('Y-m-d\TH:i')) }}"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('fecha_recepcion') border-red-400 focus:border-red-500 focus:ring-red-100 dark:focus:ring-red-900/50 @enderror"
                        >

                        @error('fecha_recepcion')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-300">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Estado --}}
                    <div>

                        <label
                            for="estado"
                            class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Estado
                            <span class="block text-xs font-normal text-slate-500">El estado se gestiona desde el flujo operativo.</span>
                            <span class="text-red-500">*</span>
                        </label>

                        @php
                            $estadoActual = old(
                                'estado',
                                $lote->estado instanceof \BackedEnum
                                    ? $lote->estado->value
                                    : $lote->estado
                            );
                        @endphp

                        <select
                            id="estado"
                            name="estado"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('estado') border-red-400 focus:border-red-500 focus:ring-red-100 dark:focus:ring-red-900/50 @enderror"
                        >

                            @foreach ($estados as $estado)

                                @php
                                    $estadoValue = $estado instanceof \BackedEnum
                                        ? $estado->value
                                        : $estado;
                                @endphp

                                <option
                                    value="{{ $estadoValue }}"
                                    @selected($estadoActual === $estadoValue)
                                >
                                    {{ $estadoValue }}
                                </option>

                            @endforeach

                        </select>

                        @error('estado')
                            <p class="mt-1.5 text-sm text-red-600 dark:text-red-300">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

                <x-ui.master-select name="unidad_medida" kind="unidad" label="Unidad de peso *" :value="$lote->unidad_medida" required weight-only />
                <p class="text-xs text-slate-500 dark:text-slate-400">El peso se convierte automáticamente a kilogramos para los reportes.</p>
                {{-- Separador --}}
                <div class="border-t border-slate-100 dark:border-slate-700"></div>

                {{-- Acciones --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a wire:navigate
                        href="{{ route('lotes.show', $lote->id_lote) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-100 dark:focus:ring-slate-700"
                    >
                        Cancelar
                    </a>

                    <x-ui.button type="submit">
                        <x-ui.icon
                            name="save"
                            class="mr-2 h-4 w-4"
                        />

                        Guardar cambios
                    </x-ui.button>

                </div>

            </form>

        </x-ui.card>

    </div>

</x-layouts.app>
