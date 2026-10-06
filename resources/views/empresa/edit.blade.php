<x-layouts.app title="Mi empresa">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="space-y-2">
            <x-ui.back-link :href="route('dashboard')" label="Volver al dashboard" />
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-brand-700">Contexto empresarial</p>
                    <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ $empresa->nombre_comercial }}</h2>
                    <p class="mt-1 text-sm text-slate-500">Tu cuenta siempre trabaja dentro de esta empresa. No se selecciona empresa al iniciar sesión.</p>
                </div>
                <span @class(['inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold','bg-emerald-50 text-emerald-700' => $empresa->estado,'bg-slate-100 text-slate-600' => ! $empresa->estado])>{{ $empresa->estado ? 'Empresa activa' : 'Empresa inactiva' }}</span>
            </div>
        </div>

        @if ($canEdit)
            <form method="POST" action="{{ route('empresa.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <x-ui.card title="Datos corporativos" description="El estado de la empresa queda reservado para la futura administración global de AgroCloud.">
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-ui.input name="ruc" label="RUC" :value="$empresa->ruc" required />
                        <x-ui.input name="razon_social" label="Razón social" :value="$empresa->razon_social" required />
                        <x-ui.input name="nombre_comercial" label="Nombre comercial" :value="$empresa->nombre_comercial" required />
                        <x-ui.input name="grupo_empresarial" label="Grupo empresarial" :value="$empresa->grupo_empresarial" />
                        <x-ui.input name="tipo_empresa" label="Tipo de empresa" :value="$empresa->tipo_empresa" placeholder="Productora, acopio, empaque, agroquímicos..." />
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
                    <div class="mt-5 space-y-2"><label for="descripcion" class="block text-sm font-medium text-slate-700">Descripción</label><textarea id="descripcion" name="descripcion" rows="4" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100">{{ old('descripcion', $empresa->descripcion) }}</textarea></div>
                    <div class="mt-5 grid gap-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div><label for="logo" class="block text-sm font-medium text-slate-700">Logo</label><input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full text-sm text-slate-600">@error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                        @if ($empresa->logo_url)<img src="{{ $empresa->logo_url }}" alt="Logo de {{ $empresa->nombre_comercial }}" class="h-20 w-20 rounded-xl border border-white bg-white object-contain p-2 shadow-sm">@endif
                    </div>
                    <div class="mt-6 flex justify-end"><x-ui.button type="submit"><x-ui.icon name="save" class="mr-2 h-4 w-4" />Guardar empresa</x-ui.button></div>
                </x-ui.card>
            </form>
        @else
            <x-ui.card title="Información corporativa" description="Tienes acceso de consulta. La edición corresponde al administrador de la empresa.">
                <div class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                    @foreach ([
                        'RUC' => $empresa->ruc,
                        'Razón social' => $empresa->razon_social,
                        'Nombre comercial' => $empresa->nombre_comercial,
                        'Grupo empresarial' => $empresa->grupo_empresarial,
                        'Tipo de empresa' => $empresa->tipo_empresa,
                        'Rubro' => $empresa->rubro,
                        'Teléfono' => $empresa->telefono,
                        'Correo' => $empresa->correo,
                        'Dirección' => $empresa->direccion,
                        'Distrito' => $empresa->distrito,
                        'Provincia' => $empresa->provincia,
                        'Departamento' => $empresa->departamento,
                    ] as $label => $value)
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</p><p class="mt-1 text-sm font-medium text-slate-900">{{ $value ?: '—' }}</p></div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    </div>
</x-layouts.app>
