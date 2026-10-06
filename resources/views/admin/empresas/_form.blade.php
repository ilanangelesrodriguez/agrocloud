@php($editing = isset($empresa))

<div class="space-y-8">
    <section>
        <div class="mb-4 flex items-center gap-3">
            <div class="grid h-9 w-9 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-ui.icon name="building" class="h-5 w-5" /></div>
            <div><h3 class="text-sm font-semibold text-slate-900">Identificación</h3><p class="text-xs text-slate-500">Datos principales y comerciales del tenant.</p></div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <x-ui.input name="ruc" label="RUC" :value="$editing ? $empresa->ruc : null" maxlength="11" required />
            <x-ui.input name="nombre_comercial" label="Nombre comercial" :value="$editing ? $empresa->nombre_comercial : null" required />
            <div class="md:col-span-2"><x-ui.input name="razon_social" label="Razón social" :value="$editing ? $empresa->razon_social : null" required /></div>
            <x-ui.input name="grupo_empresarial" label="Grupo empresarial" :value="$editing ? $empresa->grupo_empresarial : null" placeholder="Ej. ARIS" />
            <x-ui.input name="tipo_empresa" label="Tipo de empresa" :value="$editing ? $empresa->tipo_empresa : null" placeholder="Productora, acopio, empaque..." />
            <div class="md:col-span-2"><x-ui.input name="rubro" label="Rubro" :value="$editing ? $empresa->rubro : null" /></div>
        </div>
    </section>

    <section class="border-t border-slate-100 pt-7">
        <div class="mb-4 flex items-center gap-3">
            <div class="grid h-9 w-9 place-items-center rounded-xl bg-slate-100 text-slate-600"><x-ui.icon name="briefcase" class="h-5 w-5" /></div>
            <div><h3 class="text-sm font-semibold text-slate-900">Contacto y ubicación</h3><p class="text-xs text-slate-500">Información corporativa para reportes y trazabilidad.</p></div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <x-ui.input name="correo" label="Correo corporativo" type="email" :value="$editing ? $empresa->correo : null" />
            <x-ui.input name="telefono" label="Teléfono" :value="$editing ? $empresa->telefono : null" />
            <x-ui.input name="sitio_web" label="Sitio web" type="url" :value="$editing ? $empresa->sitio_web : null" placeholder="https://..." />
            <x-ui.input name="ubigeo" label="Ubigeo" :value="$editing ? $empresa->ubigeo : null" maxlength="6" />
            <x-ui.input name="departamento" label="Departamento" :value="$editing ? $empresa->departamento : null" />
            <x-ui.input name="provincia" label="Provincia" :value="$editing ? $empresa->provincia : null" />
            <x-ui.input name="distrito" label="Distrito" :value="$editing ? $empresa->distrito : null" />
            <x-ui.input name="direccion" label="Dirección" :value="$editing ? $empresa->direccion : null" />
        </div>
    </section>

    <section class="border-t border-slate-100 pt-7">
        <div class="grid gap-6 lg:grid-cols-[1fr_260px]">
            <div>
                <label for="descripcion" class="block text-sm font-medium text-slate-700">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="5" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">{{ old('descripcion', $editing ? $empresa->descripcion : '') }}</textarea>
                @error('descripcion')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                <label for="logo" class="block text-sm font-semibold text-slate-800">Logo de empresa</label>
                @if ($editing && $empresa->logo_url)
                    <div class="mt-3 flex h-24 items-center justify-center rounded-xl border border-slate-200 bg-white p-3"><img src="{{ $empresa->logo_url }}" alt="Logo" class="max-h-16 max-w-full object-contain"></div>
                @endif
                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-3 block w-full text-xs text-slate-600">
                <p class="mt-2 text-xs leading-5 text-slate-400">JPG, PNG o WEBP · máximo 4 MB · Cloudinary.</p>
                @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="border-t border-slate-100 pt-6">
        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
            <input type="hidden" name="estado" value="0">
            <input type="checkbox" name="estado" value="1" @checked(old('estado', $editing ? $empresa->estado : true)) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            <span><span class="block text-sm font-semibold text-slate-800">Empresa activa</span><span class="mt-1 block text-xs leading-5 text-slate-500">Al desactivarla, sus usuarios dejarán de acceder mientras el tenant permanezca inactivo.</span></span>
        </label>
    </section>
</div>
