@php($editing = isset($empresa))
@php($empresaActual = $editing ? $empresa : null)

<div class="space-y-6" data-company-form>
    <x-ui.ruc-lookup :endpoint="route('admin.consulta-ruc')" />
    @if($empresaActual)<x-ui.sunat-status :verification="$empresaActual->sunat_verificacion" />@endif


    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="min-w-0 space-y-6">
            {{-- Identidad y datos de la empresa --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="company-identity-title">
                <div class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 px-5 py-3.5 sm:px-5">
                    <div class="flex items-start gap-3">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 dark:bg-brand-900/35 text-brand-700 dark:text-brand-300"><x-ui.icon name="building" class="h-5 w-5" /></div>
                        <div>
                            <h3 id="company-identity-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Datos de la empresa</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Identificación legal y actividad comercial.</p>
                        </div>
                    </div>
                </div>
                <div class="grid gap-x-5 gap-y-4 p-5 sm:grid-cols-2">
                    <x-ui.input name="ruc" label="RUC *" :value="$empresaActual?->ruc" placeholder="Ej. 20550844321" maxlength="11" inputmode="numeric" autocomplete="off" required />
                    <x-ui.input name="nombre_comercial" label="Nombre comercial *" :value="$empresaActual?->nombre_comercial" placeholder="Ej. Agroexportadora del Sur" maxlength="150" autocomplete="organization" required />
                    <div class="sm:col-span-2">
                        <x-ui.input name="razon_social" label="Razón social *" :value="$empresaActual?->razon_social" placeholder="Ej. Agroexportadora del Sur S.A.C." maxlength="200" required />
                    </div>
                    <x-ui.master-select kind="grupo" name="grupo_empresarial" label="Grupo empresarial" :value="$empresaActual?->grupo_empresarial" placeholder="Ej. Grupo Agrícola del Sur (opcional)" maxlength="150" />
                    <x-ui.master-select kind="tipo_empresa" name="tipo_empresa" label="Tipo de empresa" :value="$empresaActual?->tipo_empresa" placeholder="Ej. Productora, acopio o empaque" maxlength="80" />
                    <div class="sm:col-span-2">
                        <x-ui.master-select kind="rubro" name="rubro" label="Rubro o actividad principal" :value="$empresaActual?->rubro" placeholder="Ej. Producción y exportación de arándanos" maxlength="150" />
                    </div>
                </div>
            </section>

            {{-- Información de contacto --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="company-contact-title">
                <div class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 px-5 py-4 sm:px-6">
                    <div class="flex items-start gap-3">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300"><x-ui.icon name="user" class="h-5 w-5" /></div>
                        <div>
                            <h3 id="company-contact-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Contacto</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Canales corporativos para comunicarse con la empresa.</p>
                        </div>
                    </div>
                </div>
                <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                    <x-ui.input name="correo" label="Correo corporativo" type="email" :value="$empresaActual?->correo" placeholder="Ej. contacto@empresa.pe" autocomplete="email" maxlength="255" />
                    <x-ui.input name="telefono" label="Teléfono de contacto" type="tel" :value="$empresaActual?->telefono" placeholder="Ej. +51 999 888 777" autocomplete="tel" inputmode="tel" maxlength="30" title="Ingresa entre 7 y 15 dígitos; puedes usar +, espacios, guiones o paréntesis." />
                    <div class="sm:col-span-2">
                        <div>
                            <x-ui.input name="sitio_web" label="Sitio web" type="text" inputmode="url" autocapitalize="none" spellcheck="false" :value="$empresaActual?->sitio_web" placeholder="Ej. misitio.com" aria-describedby="company-site-help" autocomplete="url" maxlength="255" />
                            <p id="company-site-help" class="mt-2 text-xs text-slate-500 dark:text-slate-400">Puedes escribir misitio.com; se guardará con https:// si no indicas un protocolo.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Ubicación en Perú; el catálogo se carga sólo en estos formularios --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="company-location-title" data-company-location>
                <div class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 px-5 py-4 sm:px-6">
                    <div class="flex items-start gap-3">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 dark:bg-brand-900/35 text-brand-700 dark:text-brand-300"><x-ui.icon name="building" class="h-5 w-5" /></div>
                        <div>
                            <h3 id="company-location-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Ubicación de la sede</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Escribe para buscar; provincia y distrito dependen de la selección anterior.</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-5 p-5 sm:p-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200" for="departamento">Departamento</label>
                            <input id="departamento" name="departamento" type="text" list="company-departments" data-geo-field="departamento" value="{{ old('departamento', $empresaActual?->departamento) }}" placeholder="Escribe o selecciona, ej. LIMA" maxlength="100" autocomplete="off" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('departamento') border-red-400 @enderror" aria-describedby="geo-status" />
                            <datalist id="company-departments"></datalist>
                            @error('departamento')<p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200" for="provincia">Provincia</label>
                            <input id="provincia" name="provincia" type="text" list="company-provinces" data-geo-field="provincia" value="{{ old('provincia', $empresaActual?->provincia) }}" placeholder="Primero elige un departamento" maxlength="100" autocomplete="off" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('provincia') border-red-400 @enderror" aria-describedby="geo-status" />
                            <datalist id="company-provinces"></datalist>
                            @error('provincia')<p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200" for="distrito">Distrito</label>
                            <input id="distrito" name="distrito" type="text" list="company-districts" data-geo-field="distrito" value="{{ old('distrito', $empresaActual?->distrito) }}" placeholder="Primero elige una provincia" maxlength="100" autocomplete="off" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('distrito') border-red-400 @enderror" aria-describedby="geo-status" />
                            <datalist id="company-districts"></datalist>
                            @error('distrito')<p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200" for="ubigeo">Código UBIGEO</label>
                            <input id="ubigeo" name="ubigeo" type="text" data-geo-field="ubigeo" value="{{ old('ubigeo', $empresaActual?->ubigeo) }}" placeholder="Se completará con el distrito" inputmode="numeric" maxlength="6" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('ubigeo') border-red-400 @enderror" aria-describedby="geo-status" />
                            @error('ubigeo')<p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <p id="geo-status" role="status" aria-live="polite" class="rounded-xl bg-brand-50 dark:bg-brand-900/35 px-3.5 py-2.5 text-xs leading-5 text-brand-800 dark:text-brand-200" data-geo-status>Preparando búsqueda de departamentos, provincias y distritos…</p>
                    <div>
                        <x-ui.input name="direccion" label="Dirección de la sede" :value="$empresaActual?->direccion" placeholder="Ej. Av. Industrial 120, Parque Agroindustrial" maxlength="250" autocomplete="street-address" />
                        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Añade calle, número y referencia. El distrito se registra por separado.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm sm:p-6" aria-labelledby="company-description-title">
                <h3 id="company-description-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Descripción de la empresa</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Opcional. Resume la actividad o especialidad principal (máximo 1000 caracteres).</p>
                <textarea id="descripcion" name="descripcion" rows="4" maxlength="1000" data-company-description class="mt-4 w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-3 text-sm leading-6 text-slate-900 dark:text-slate-100 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('descripcion') border-red-400 @enderror" placeholder="Ej. Empresa dedicada a la producción, acopio y procesamiento de productos agrícolas para el mercado nacional e internacional.">{{ old('descripcion', $empresaActual?->descripcion) }}</textarea>
                <div class="mt-2 flex items-start justify-between gap-3">
                    @error('descripcion')<p class="text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@else<span class="text-xs text-slate-500 dark:text-slate-400">Este texto puede aparecer en la ficha corporativa.</span>@enderror
                    <span class="shrink-0 text-xs tabular-nums text-slate-500 dark:text-slate-400" data-description-counter>0 / 1000</span>
                </div>
            </section>
        </div>

        {{-- Acciones y logo: visibles junto a los datos principales en pantallas grandes --}}
        <aside class="min-w-0 space-y-5 xl:sticky xl:top-24" aria-label="Identidad visual y estado de la empresa">
            <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="company-logo-title">
                <div class="border-b border-slate-100 dark:border-slate-700 px-5 py-3.5">
                    <h3 id="company-logo-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Logo de la empresa</h3>
                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Se utiliza para identificar a la empresa en AgroCloud.</p>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex h-28 items-center justify-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-3" data-logo-preview>
                        <img data-logo-image @if($empresaActual?->logo_url) src="{{ $empresaActual->logo_url }}" @endif alt="Vista previa del logo empresarial" class="max-h-24 max-w-full object-contain {{ $empresaActual?->logo_url ? '' : 'hidden' }}" />
                        <div data-logo-empty class="flex flex-col items-center gap-2 text-slate-400 {{ $empresaActual?->logo_url ? 'hidden' : '' }}">
                            <x-ui.icon name="building" class="h-10 w-10" />
                            <span class="text-xs font-medium">Aún no hay logo</span>
                        </div>
                    </div>
                    <div data-logo-dropzone class="rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50/70 dark:bg-slate-800/70 px-3 py-3.5 text-center transition hover:border-brand-400 hover:bg-brand-50/40 dark:hover:bg-brand-900/35">
                        <label for="logo" role="button" tabindex="0" data-logo-trigger class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-brand-200 dark:border-brand-800 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-brand-700 dark:text-brand-300 shadow-sm hover:bg-brand-50 dark:hover:bg-brand-900/35 focus:outline-none focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50">
                            {{ $editing && $empresaActual?->logo_url ? 'Cambiar logo' : 'Seleccionar imagen' }}
                        </label>
                        <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-logo-input aria-describedby="logo-help logo-feedback" />
                        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">O arrastra una imagen aquí</p>
                        <p id="logo-help" class="mt-1.5 text-xs leading-5 text-slate-500 dark:text-slate-400">PNG, JPG o WEBP · máximo 4 MB. Se recomienda imagen cuadrada con fondo transparente.</p>
                    </div>
                    <p id="logo-feedback" data-logo-feedback role="status" aria-live="polite" class="break-all text-xs leading-5 text-slate-600 dark:text-slate-300">{{ $editing && $empresaActual?->logo_url ? 'El logo actual se mantendrá si no eliges otro.' : 'Puedes registrar la empresa sin logo.' }}</p>
                    @error('logo')<p class="text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                </div>
            </section>
            <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm" aria-labelledby="company-status-title">
                <h3 id="company-status-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Acceso de la empresa</h3>
                <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50 dark:bg-slate-800 p-3.5">
                    <input type="hidden" name="estado" value="0" />
                    <input type="checkbox" name="estado" value="1" @checked(old('estado', $editing ? $empresaActual?->estado : true)) class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 dark:border-slate-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500" />
                    <span>
                        <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Empresa activa</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">Si desactivas la empresa, sus usuarios no podrán acceder.</span>
                    </span>
                </label>
            </section>
            <div class="rounded-2xl border border-brand-100 dark:border-brand-800 bg-brand-50/70 dark:bg-brand-900/35 p-4 text-xs leading-5 text-brand-900 dark:text-brand-200">
                <p class="font-semibold">Antes de guardar</p>
                <p class="mt-1">Confirma el RUC y la ubicación. Los campos obligatorios están identificados en su etiqueta.</p>
            </div>
        </aside>
    </div>
</div>
