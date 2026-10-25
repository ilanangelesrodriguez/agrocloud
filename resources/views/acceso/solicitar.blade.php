<x-layouts.guest title="Solicitar acceso empresarial" :landing="true">
    <x-ui.public-header />
    <main class="mx-auto max-w-4xl space-y-8 px-5 py-10 sm:py-14">
        <div class="space-y-5">
            <x-ui.back-link :href="url('/')" label="Volver al inicio" />
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-widest text-brand-700 dark:text-brand-300">Acceso empresarial</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-slate-100 sm:text-4xl">Tu operación, conectada con AgroCloud AI</h1>
                <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">Registra tu empresa para comenzar a gestionar recepciones, inspecciones y calidad en un solo lugar. Revisaremos tu solicitud y te comunicaremos el resultado por correo.</p>
            </div>
        </div>

        @if(!$habilitado)
            <x-ui.card title="Solicitudes de acceso no disponibles"><p class="text-sm leading-6 text-slate-500">Estamos preparando el registro de nuevas empresas. Si ya tienes una cuenta, puedes ingresar con tus credenciales.</p></x-ui.card>
        @elseif($tipos->isEmpty())
            <x-ui.card title="Formulario temporalmente no disponible"><p class="text-sm leading-6 text-slate-500">Estamos actualizando las opciones de registro. Por favor, vuelve a intentarlo más adelante.</p></x-ui.card>
        @else
            <ol aria-label="Proceso de acceso" class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900 sm:grid-cols-3 sm:p-6">
                @foreach([['Envía tu solicitud', 'Comparte los datos de tu empresa.'], ['Revisamos la información', 'Recibe la respuesta por correo.'], ['Activa tu cuenta', 'Accede mediante una invitación.']] as [$title, $description])
                    <li class="flex items-start gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-semibold text-brand-700 dark:bg-brand-950/50 dark:text-brand-300">{{ $loop->iteration }}</span>
                        <div><p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $title }}</p><p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $description }}</p></div>
                    </li>
                @endforeach
            </ol>

            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
                <p>Completa los campos marcados con <span class="font-semibold text-slate-700 dark:text-slate-200">*</span>.</p>
                <p>No necesitas crear una contraseña todavía.</p>
            </div>

            <form data-ajax-form method="POST" action="{{ route('acceso.store') }}" class="space-y-6">
                @csrf
                <x-ui.card title="1. Información de la empresa" description="Ingresa el RUC para completar la razón social y revisa los datos legales.">
                    <div class="grid items-start gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div class="space-y-2">
                            <x-ui.input name="ruc" label="RUC *" required minlength="11" maxlength="11" pattern="[0-9]{11}" inputmode="numeric" autocomplete="off" placeholder="Ingresa los 11 dígitos" />
                            <x-ui.ruc-lookup :endpoint="route('acceso.consulta-ruc')" method="POST" help="La razón social se completa al terminar de ingresar el RUC." />
                        </div>
                        <x-ui.input name="nombre_comercial" label="Nombre comercial *" required maxlength="150" autocomplete="organization" placeholder="Ej. Agroexportadora Los Olivos" />
                        <div class="sm:col-span-2">
                            <x-ui.input name="razon_social" label="Razón social *" required maxlength="200" autocomplete="off" placeholder="Se completa con el RUC o puedes ingresarla manualmente" />
                        </div>
                        <div class="space-y-2 sm:col-span-2">
                            <label for="tipo_empresa" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo de empresa *</label>
                            <select id="tipo_empresa" name="tipo_empresa" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50">
                                <option value="" disabled @selected(!old('tipo_empresa'))>Selecciona la actividad de tu empresa</option>
                                @foreach($tipos as $tipo)<option value="{{ $tipo }}" @selected(old('tipo_empresa') === $tipo)>{{ $tipo }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card title="2. Datos del representante" description="Indica quién será el contacto principal para revisar la solicitud y recibir la invitación.">
                    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                        <x-ui.input name="nombre" label="Nombres *" required maxlength="100" autocomplete="given-name" placeholder="Ej. María Fernanda" />
                        <x-ui.input name="apellido" label="Apellidos *" required maxlength="100" autocomplete="family-name" placeholder="Ej. García López" />
                        <div class="space-y-2">
                            <x-ui.input name="correo" label="Correo de contacto *" type="email" required maxlength="254" autocomplete="email" placeholder="Ej. nombre@tuempresa.com" aria-describedby="correo-help" />
                            <p id="correo-help" class="text-xs leading-5 text-slate-500 dark:text-slate-400">Enviaremos la respuesta y el enlace de activación a este correo.</p>
                        </div>
                        <x-ui.input name="telefono" label="Teléfono de contacto *" type="tel" required maxlength="30" autocomplete="tel" placeholder="Ej. +51 987 654 321" />
                    </div>
                </x-ui.card>

                <x-ui.card title="3. Cuéntanos sobre tu operación" description="Esta información nos ayuda a entender las necesidades de tu empresa.">
                    <div class="space-y-2">
                        <label for="uso" class="block text-sm font-medium text-slate-700 dark:text-slate-200">¿Cómo planeas utilizar AgroCloud AI? *</label>
                        <textarea id="uso" name="uso" required minlength="20" maxlength="2000" rows="4" aria-describedby="uso-help" placeholder="Ej. Somos una planta de empaque de palta y necesitamos registrar las recepciones, realizar inspecciones de calidad y dar seguimiento a cada lote." class="w-full resize-y rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50">{{ old('uso') }}</textarea>
                        <p id="uso-help" class="text-xs leading-5 text-slate-500 dark:text-slate-400">Incluye tu actividad, los productos que manejas y lo que necesitas mejorar. Entre 20 y 2,000 caracteres.</p>
                    </div>
                    <div class="mt-6 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                        <label class="flex cursor-pointer items-start gap-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            <input type="checkbox" name="consentimiento" value="1" required @checked(old('consentimiento')) class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 accent-brand-600 focus:ring-brand-500">
                            <span>Autorizo a AgroCloud AI a utilizar estos datos para evaluar la solicitud y contactar a mi empresa.</span>
                        </label>
                    </div>
                    <div aria-hidden="true" class="hidden"><label>Dejar vacío<input name="sitio_contacto" tabindex="-1" autocomplete="off"></label></div>
                </x-ui.card>

                <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <p class="max-w-md text-xs leading-6 text-slate-500 dark:text-slate-400">Revisa tus datos antes de enviar. Si tu empresa es aprobada, recibirás una invitación para activar tu cuenta administradora.</p>
                    <x-ui.button type="submit" class="shrink-0"><x-ui.icon name="building-plus" class="mr-2 h-4 w-4" />Enviar solicitud</x-ui.button>
                </div>
            </form>
        @endif
    </main>
</x-layouts.guest>
