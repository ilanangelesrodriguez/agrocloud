<x-layouts.app title="Nuevo productor">

    <div class="space-y-6">

        {{-- Encabezado --}}
        <div class="flex flex-col gap-3">

            <div>
                <a
                    href="{{ route('productores.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-brand-700"
                >
                    <x-ui.icon
                        name="arrow-left"
                        class="h-4 w-4"
                    />

                    Regresar a productores
                </a>
            </div>

            <div>
                <h1 class="text-2xl font-semibold text-slate-900">
                    Nuevo productor
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Registra la información del productor para tu empresa.
                </p>
            </div>

        </div>

        {{-- Errores de validación --}}
        @if ($errors->any())

            <x-ui.alert type="error">

                <div class="font-semibold">
                    No se pudo registrar el productor.
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
            title="Información del productor"
            description="Completa los datos solicitados para registrar un nuevo productor."
        >

            <form
                method="POST"
                action="{{ route('productores.store') }}"
                class="space-y-6"
            >

                @csrf

                {{-- Datos principales --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- RUC --}}
                    <div>

                        <label
                            for="ruc"
                            class="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            RUC
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="ruc"
                            name="ruc"
                            type="text"
                            inputmode="numeric"
                            maxlength="11"
                            value="{{ old('ruc') }}"
                            placeholder="Ej. 20550844321"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('ruc') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                        >

                        @error('ruc')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-1.5 text-xs text-slate-500">
                            Debe contener exactamente 11 dígitos.
                        </p>

                    </div>

                    {{-- Nombre / razón social --}}
                    <div>

                        <label
                            for="nombre_razon_social"
                            class="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            Nombre / razón social
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="nombre_razon_social"
                            name="nombre_razon_social"
                            type="text"
                            maxlength="200"
                            value="{{ old('nombre_razon_social') }}"
                            placeholder="Nombre o razón social del productor"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('nombre_razon_social') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                        >

                        @error('nombre_razon_social')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Fundo --}}
                    <div>

                        <label
                            for="fundo"
                            class="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            Fundo
                        </label>

                        <input
                            id="fundo"
                            name="fundo"
                            type="text"
                            maxlength="150"
                            value="{{ old('fundo') }}"
                            placeholder="Nombre del fundo"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('fundo') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                        >

                        @error('fundo')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Ubicación --}}
                    <div>

                        <label
                            for="ubicacion"
                            class="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            Ubicación
                        </label>

                        <input
                            id="ubicacion"
                            name="ubicacion"
                            type="text"
                            maxlength="250"
                            value="{{ old('ubicacion') }}"
                            placeholder="Ubicación del fundo"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 @error('ubicacion') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                        >

                        @error('ubicacion')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

                {{-- Separador --}}
                <div class="border-t border-slate-100"></div>

                {{-- Acciones --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a
                        href="{{ route('productores.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100"
                    >
                        Cancelar
                    </a>

                    <x-ui.button type="submit">
                        <x-ui.icon
                            name="save"
                            class="mr-2 h-4 w-4"
                        />

                        Guardar productor
                    </x-ui.button>

                </div>

            </form>

        </x-ui.card>

    </div>

</x-layouts.app>
