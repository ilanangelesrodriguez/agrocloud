<x-layouts.app title="Empresa">
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-slate-950">Datos de la empresa</h2>
            <p class="mt-1 text-sm text-slate-500">Información corporativa usada para separar usuarios y, más adelante, lotes, inspecciones y reportes.</p>
        </div>

        <form method="POST" action="{{ route('empresa.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <x-ui.card>
                <div class="grid gap-5 md:grid-cols-2">
                    <x-ui.input name="ruc" label="RUC" :value="$empresa->ruc" required />
                    <x-ui.input name="razon_social" label="Razón social" :value="$empresa->razon_social" required />
                    <x-ui.input name="nombre_comercial" label="Nombre comercial" :value="$empresa->nombre_comercial" required />
                    <x-ui.input name="grupo_empresarial" label="Grupo empresarial" :value="$empresa->grupo_empresarial" />
                    <x-ui.input name="tipo_empresa" label="Tipo de empresa" :value="$empresa->tipo_empresa" placeholder="Ej. Productora, acopio, empaque, agroquímicos" />
                    <x-ui.input name="rubro" label="Rubro" :value="$empresa->rubro" />
                    <x-ui.input name="telefono" label="Teléfono" :value="$empresa->telefono" />
                    <x-ui.input name="correo" label="Correo corporativo" type="email" :value="$empresa->correo" />
                    <x-ui.input name="sitio_web" label="Sitio web" type="url" :value="$empresa->sitio_web" />
                    <x-ui.input name="ubigeo" label="Ubigeo" :value="$empresa->ubigeo" maxlength="6" />
                    <x-ui.input name="departamento" label="Departamento" :value="$empresa->departamento" />
                    <x-ui.input name="provincia" label="Provincia" :value="$empresa->provincia" />
                    <x-ui.input name="distrito" label="Distrito" :value="$empresa->distrito" />
                    <x-ui.input name="direccion" label="Dirección" :value="$empresa->direccion" />
                </div>

                <div class="mt-5 space-y-2">
                    <label for="descripcion" class="block text-sm font-medium text-slate-700">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">{{ old('descripcion', $empresa->descripcion) }}</textarea>
                    @error('descripcion')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mt-5 grid gap-5 md:grid-cols-[1fr_auto] md:items-end">
                    <div>
                        <label for="logo" class="block text-sm font-medium text-slate-700">Logo</label>
                        <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full text-sm text-slate-600">
                        @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @if ($empresa->logo_url)
                        <img src="{{ $empresa->logo_url }}" alt="Logo de {{ $empresa->nombre_comercial }}" class="h-20 w-20 rounded-xl border border-slate-200 object-contain p-2">
                    @endif
                </div>

                <label class="mt-5 flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="estado" value="0">
                    <input type="checkbox" name="estado" value="1" @checked(old('estado', $empresa->estado)) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Empresa activa
                </label>

                <div class="mt-6 flex justify-end">
                    <x-ui.button type="submit">Guardar cambios</x-ui.button>
                </div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
