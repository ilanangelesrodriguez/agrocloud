<x-layouts.app title="Mi empresa">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="space-y-4">
            <x-ui.back-link :href="route('dashboard')" label="Volver al dashboard" />
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-300">Mi empresa / {{ $canEdit ? 'Edición' : 'Consulta' }}</p>
                    <h2 class="mt-2 break-words text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-100 sm:text-3xl">{{ $empresa->nombre_comercial }}</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">Tu cuenta siempre trabaja dentro de esta empresa. No se selecciona empresa al iniciar sesión.</p>
                </div>
                <x-ui.status-badge :active="$empresa->estado" active-label="Empresa activa" inactive-label="Empresa inactiva" />
            </div>
        </div>

        @if ($canEdit)
            <form method="POST" action="{{ route('empresa.update') }}" enctype="multipart/form-data" data-company-form data-ajax-form>
                @csrf
                @method('PUT')
                <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0 space-y-5">
                        <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="company-data-title">
                            <div class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 px-5 py-3.5">
                                <h3 id="company-data-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Datos de la empresa</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Identificación legal y actividad comercial.</p>
                            </div>
                            <div class="grid gap-x-5 gap-y-4 p-5 sm:grid-cols-2">
                                <x-ui.input name="ruc" label="RUC *" :value="$empresa->ruc" placeholder="Ej. 20550844321" maxlength="11" required />
                                <x-ui.input name="nombre_comercial" label="Nombre comercial *" :value="$empresa->nombre_comercial" placeholder="Ej. Agroexportadora del Sur" required />
                                <div class="sm:col-span-2"><x-ui.input name="razon_social" label="Razón social *" :value="$empresa->razon_social" placeholder="Ej. Agroexportadora del Sur S.A.C." required /></div>
                                <x-ui.master-select kind="grupo" name="grupo_empresarial" label="Grupo empresarial" :value="$empresa->grupo_empresarial" placeholder="Ej. Grupo Agrícola del Sur" />
                                <x-ui.master-select kind="tipo_empresa" name="tipo_empresa" label="Tipo de empresa" :value="$empresa->tipo_empresa" placeholder="Ej. Productora, acopio o empaque" />
                                <div class="sm:col-span-2"><x-ui.master-select kind="rubro" name="rubro" label="Rubro o actividad principal" :value="$empresa->rubro" placeholder="Ej. Producción y exportación de arándanos" /></div>
                            </div>
                        </section>

                        <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="company-contact-title" data-company-location>
                            <div class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 px-5 py-3.5">
                                <h3 id="company-contact-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Contacto y ubicación</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Canales corporativos y dirección de la sede.</p>
                            </div>
                            <div class="grid gap-x-5 gap-y-4 p-5 sm:grid-cols-2">
                                <x-ui.input name="telefono" label="Teléfono" type="tel" :value="$empresa->telefono" placeholder="Ej. +51 999 888 777" autocomplete="tel" inputmode="tel" maxlength="30" pattern="^\+?[0-9][0-9\s().-]*$" title="Ingresa entre 7 y 15 dígitos; puedes usar +, espacios, guiones o paréntesis." />
                                <x-ui.input name="correo" label="Correo corporativo" type="email" :value="$empresa->correo" placeholder="Ej. contacto@empresa.pe" />
                                <x-ui.input name="sitio_web" label="Sitio web" type="url" :value="$empresa->sitio_web" placeholder="https://www.empresa.pe" />
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200" for="departamento">Departamento</label>
                                    <select id="departamento" name="departamento" data-geo-field="departamento" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50" aria-describedby="geo-status"><option value="{{ old('departamento', $empresa->departamento) }}">{{ old('departamento', $empresa->departamento) ?: 'Selecciona una opción' }}</option></select>
                                    <datalist id="company-departments"></datalist>
                                    @error('departamento')<p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200" for="provincia">Provincia</label>
                                    <select id="provincia" name="provincia" data-geo-field="provincia" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50" aria-describedby="geo-status"><option value="{{ old('provincia', $empresa->provincia) }}">{{ old('provincia', $empresa->provincia) ?: 'Selecciona una opción' }}</option></select>
                                    <datalist id="company-provinces"></datalist>
                                    @error('provincia')<p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200" for="distrito">Distrito</label>
                                    <select id="distrito" name="distrito" data-geo-field="distrito" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50" aria-describedby="geo-status"><option value="{{ old('distrito', $empresa->distrito) }}">{{ old('distrito', $empresa->distrito) ?: 'Selecciona una opción' }}</option></select>
                                    <datalist id="company-districts"></datalist>
                                    @error('distrito')<p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200" for="ubigeo">Código UBIGEO</label>
                                    <input id="ubigeo" name="ubigeo" type="text" data-geo-field="ubigeo" value="{{ old('ubigeo', $empresa->ubigeo) }}" placeholder="Se completará con el distrito" inputmode="numeric" maxlength="6" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50 @error('ubigeo') border-red-400 @enderror" aria-describedby="geo-status">
                                    @error('ubigeo')<p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                                </div>
                                <x-ui.input name="direccion" label="Dirección de la sede" :value="$empresa->direccion" placeholder="Ej. Av. Industrial 120" />
                            </div>
                            <p id="geo-status" role="status" aria-live="polite" class="mx-5 mb-5 rounded-xl bg-brand-50 dark:bg-brand-900/35 px-3.5 py-2.5 text-xs leading-5 text-brand-800 dark:text-brand-200" data-geo-status>Preparando búsqueda de departamentos, provincias y distritos…</p>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm" aria-labelledby="company-description-title">
                            <h3 id="company-description-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Descripción de la empresa</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Opcional. Resume la actividad o especialidad principal.</p>
                            <textarea id="descripcion" name="descripcion" rows="4" maxlength="1000" class="mt-4 w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-3 text-sm leading-6 text-slate-900 dark:text-slate-100 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50" placeholder="Ej. Empresa dedicada a la producción y comercialización de productos agrícolas.">{{ old('descripcion', $empresa->descripcion) }}</textarea>
                        </section>
                    </div>

                    <aside class="min-w-0 space-y-5 xl:sticky xl:top-24" aria-label="Identidad visual de la empresa">
                        <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="company-logo-title">
                            <div class="border-b border-slate-100 dark:border-slate-700 px-5 py-3.5">
                                <h3 id="company-logo-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Logo de la empresa</h3>
                                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Identifica a la empresa dentro de AgroCloud.</p>
                            </div>
                            <div class="space-y-3 p-5">
                                <div class="flex h-28 items-center justify-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-3">
                                    <img data-company-logo @if($empresa->logo_url) src="{{ $empresa->logo_url }}" @endif alt="Vista previa del logo de {{ $empresa->nombre_comercial }}" class="max-h-24 max-w-full object-contain {{ $empresa->logo_url ? '' : 'hidden' }}">
                                    <span data-company-logo-empty class="text-xs font-medium text-slate-400 {{ $empresa->logo_url ? 'hidden' : '' }}">Aún no hay logo</span>
                                </div>
                                <div class="rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50/70 dark:bg-slate-800/70 px-3 py-3.5">
                                    <label for="logo" class="block cursor-pointer rounded-xl border border-brand-200 dark:border-brand-800 bg-white dark:bg-slate-900 px-4 py-2.5 text-center text-sm font-semibold text-brand-700 dark:text-brand-300 shadow-sm hover:bg-brand-50 dark:hover:bg-brand-900/35 focus-within:ring-4 focus-within:ring-brand-100 dark:focus-within:ring-brand-900/50">Cambiar logo</label>
                                    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" data-company-logo-input>
                                    <p class="mt-1.5 text-center text-xs text-slate-500 dark:text-slate-400">PNG, JPG o WEBP · máximo 4 MB.</p>
                                </div>
                                @error('logo')<p class="text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                            </div>
                        </section>
                        <div class="rounded-2xl border border-brand-100 dark:border-brand-800 bg-brand-50/70 dark:bg-brand-900/35 p-4 text-xs leading-5 text-brand-900 dark:text-brand-200">
                            <p class="font-semibold">Antes de guardar</p>
                            <p class="mt-1">Confirma los datos corporativos y la ubicación de la sede.</p>
                        </div>
                    </aside>
                </div>
                <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-end sm:px-6">
                    <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">Cancelar</a>
                    <x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="save" class="h-4 w-4" />Guardar empresa</span></x-ui.button>
                </div>
            </form>
            <script>
                (() => {
                    const input = document.querySelector('[data-company-logo-input]');
                    const image = document.querySelector('[data-company-logo]');
                    const empty = document.querySelector('[data-company-logo-empty]');
                    if (!input || !image || !empty) return;
                    input.addEventListener('change', () => {
                        const file = input.files?.[0];
                        if (!file || !file.type.startsWith('image/')) return;
                        image.src = URL.createObjectURL(file);
                        image.classList.remove('hidden');
                        empty.classList.add('hidden');
                    });
                })();
            </script>
            <script defer src="{{ asset('js/empresa-form.js') }}?v=20261012-geo"></script>
        @else
            <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm" aria-labelledby="company-readonly-title">
                <div class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 px-5 py-3.5">
                    <h3 id="company-readonly-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Datos corporativos</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tienes acceso de consulta. La edición corresponde al administrador de la empresa.</p>
                </div>
                <div class="grid gap-x-8 gap-y-5 p-5 sm:grid-cols-2">
                    @foreach ([
                        'RUC' => $empresa->ruc, 'Razón social' => $empresa->razon_social, 'Nombre comercial' => $empresa->nombre_comercial,
                        'Grupo empresarial' => $empresa->grupo_empresarial, 'Tipo de empresa' => $empresa->tipo_empresa, 'Rubro' => $empresa->rubro,
                        'Teléfono' => $empresa->telefono, 'Correo' => $empresa->correo, 'Dirección' => $empresa->direccion,
                        'Distrito' => $empresa->distrito, 'Provincia' => $empresa->provincia, 'Departamento' => $empresa->departamento,
                    ] as $label => $value)
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</p><p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $value ?: '—' }}</p></div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
