<x-layouts.app title="Dashboard">
    <div class="space-y-6">
        <section class="rounded-2xl bg-gradient-to-br from-brand-700 to-brand-600 p-6 text-white shadow-sm sm:p-7">
            <p class="text-sm font-medium text-brand-100">Sesión iniciada correctamente</p>
            <h2 class="mt-2 text-2xl font-semibold sm:text-3xl">
                Bienvenido, {{ $user?->nombre ?? 'Usuario' }}
            </h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-100">
                Esta es la base del flujo de AgroCloud AI. Desde aquí podrás ir incorporando los módulos sin duplicar layouts, navegación ni componentes visuales.
            </p>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Lotes activos', 'value' => 0],
                ['label' => 'Inspecciones pendientes', 'value' => 0],
                ['label' => 'Incidencias abiertas', 'value' => 0],
                ['label' => 'Detecciones IA', 'value' => 0],
            ] as $metric)
                <x-ui.card>
                    <p class="text-sm text-slate-500">{{ $metric['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $metric['value'] }}</p>
                </x-ui.card>
            @endforeach
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card title="Usuario" description="Datos de la sesión actual">
                <dl class="space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">Nombre</dt>
                        <dd class="font-medium text-slate-900">{{ $user?->nombre }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-3">
                        <dt class="text-slate-500">Correo</dt>
                        <dd class="font-medium text-slate-900">{{ $user?->correo }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Rol</dt>
                        <dd class="font-medium text-slate-900">{{ $user?->rol?->nombre ?? 'Sin rol asignado' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card title="Siguiente etapa" description="Mantén este orden para crecer sin desorganizar el proyecto">
                <ol class="space-y-3 text-sm text-slate-600">
                    <li><strong class="text-slate-900">1.</strong> Usuarios y roles.</li>
                    <li><strong class="text-slate-900">2.</strong> Productores / proveedores.</li>
                    <li><strong class="text-slate-900">3.</strong> Recepción y lotes.</li>
                    <li><strong class="text-slate-900">4.</strong> Muestreo e inspecciones.</li>
                    <li><strong class="text-slate-900">5.</strong> Evidencias, Cloudinary e IA.</li>
                </ol>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
