<x-layouts.guest title="Formulario procesado" :landing="true">
    <x-ui.public-header />
    <main class="mx-auto max-w-xl px-5 py-16">
        <x-ui.card title="Formulario procesado">
            <x-ui.icon name="check" class="mb-4 h-9 w-9 text-brand-600" />
            <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">
                Hemos procesado los datos enviados. Si corresponden a una nueva solicitud elegible,
                el equipo de AgroCloud evaluará la incorporación y comunicará el resultado al correo registrado.
            </p>
            <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">
                Por seguridad no mostramos si un RUC o correo ya pertenece a un expediente o cuenta.
                No se generan múltiples expedientes para el mismo RUC ni varias cuentas empresariales con el mismo correo.
            </p>
            <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">
                Si ya enviaste un formulario y necesitas cambiar los datos, asociar otra empresa
                o recuperar una invitación, comunícate con la administración de AgroCloud.
                Cerrar sesión o volver a enviar el formulario no reinicia la solicitud anterior.
            </p>
            <a wire:navigate href="{{ url('/') }}" class="mt-5 inline-flex items-center gap-2 font-semibold text-brand-700 dark:text-brand-300">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />Volver al inicio
            </a>
        </x-ui.card>
    </main>
</x-layouts.guest>
