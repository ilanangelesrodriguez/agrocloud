<x-layouts.app title="Editar productor">

    <div class="space-y-6">

        {{-- Navegación --}}
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
        <div>

            <h1 class="text-2xl font-semibold text-slate-900">
                Editar productor
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Actualiza la información del productor registrado.
            </p>

        </div>

        {{-- Errores de validación --}}
        @if ($errors->any())

            <x-ui.alert type="error">

                <div class="font-semibold">
                    No se pudo actualizar el productor.
                </div>

                <ul class="mt-2 list-disc space-y-1 pl-5">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </x-ui.alert>

        @endif

        {{-- Mensaje de éxito --}}
        @if (session('success'))

            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>

        @endif

        {{-- Formulario --}}
        <x-ui.card
            title="Información del productor"
            description="Modifica los datos que deseas actualizar."
        >

            <form
                method="POST"
                action="{{ route('productores.update', $productorModel->id_productor) }}"
                class="space-y-6"
            >

                @csrf

                @method('PUT')

                {{-- Datos --}}
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
                            value="{{ old('ruc', $productorModel->ruc) }}"
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
                            value="{{ old('nombre_razon_social', $productorModel->nombre_razon_social) }}"
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
                            value="{{ old('fundo', $productorModel->fundo) }}"
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
                            value="{{ old('ubicacion', $productorModel->ubicacion) }}"
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

                {{-- Información adicional --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <div class="flex gap-3">

                        <x-ui.icon
                            name="building"
                            class="mt-0.5 h-5 w-5 shrink-0 text-slate-500"
                        />

                        <div>

                            <p class="text-sm font-semibold text-slate-800">
                                Empresa asociada
                            </p>

                            <p class="mt-1 text-sm text-slate-600">
                                Este productor pertenece a la empresa con la que estás trabajando actualmente.
                            </p>

                        </div>

                    </div>

                </div>

                {{-- Separador --}}
                <div class="border-t border-slate-100"></div>

                {{-- Acciones --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                    <a
                        href="{{ route('productores.show', $productorModel->id_productor) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100"
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
