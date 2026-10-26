<x-layouts.app title="Detalle de muestreo">
    <div class="space-y-6"><x-ui.expediente-path :lote="$muestreo->lote" :muestreo="$muestreo" />
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <x-ui.back-link href="{{ route('muestreos.index') }}" label="Volver a muestreos" />
                <div class="mt-3 flex items-start gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                        <x-ui.icon name="clipboard-list" class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Muestreo #{{ $muestreo->id_muestreo }}</h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lote {{ $muestreo->lote?->codigo_lote }} · {{ $muestreo->tamano_muestra }} frutos · {{ $muestreo->metodo }}</p>
                    </div>
                </div>
            </div>
            @if(auth()->user()->rol?->codigo === 'INSPECTOR' && auth()->user()->hasPermission('inspecciones.crear') && $muestreo->estado !== 'COMPLETADO' && (!$muestreo->inspecciones->where('estado', 'VALIDADA')->count() || $muestreo->inspecciones->where('estado', 'EN_CAPTURA')->count()))
                <form data-ajax-form action="{{ route('muestreos.inspecciones.store', $muestreo->id_muestreo) }}" method="POST">@csrf
                    <x-ui.button type="submit"><span class="inline-flex items-center gap-2"><x-ui.icon name="camera" class="h-4 w-4" />Iniciar / continuar inspección</span></x-ui.button>
                </form>
            @endif
        </div>
        <x-ui.steps :steps="['Muestra registrada', 'Inspección', 'Dictamen']" :active="$muestreo->estado === 'REGISTRADO' ? 1 : ($muestreo->estado === 'EN_INSPECCION' ? 2 : 3)" />
        @include('inspecciones._mensajes')
        <x-ui.card title="Protocolo de muestreo" description="El protocolo y su justificación quedan conservados en la trazabilidad.">
            @if($protocolo)<dl class="grid gap-4 text-sm md:grid-cols-2">@foreach(['protocolo' => 'Protocolo y versión', 'criterio_muestra' => 'Criterio del tamaño', 'seleccion_frutos' => 'Selección y cobertura', 'poblacion_estimada' => 'Población estimada de frutos', 'fuente_poblacion' => 'Fuente de la estimación', 'modalidad' => 'Modalidad de registro', 'motivo_excepcion' => 'Justificación de la excepción manual', 'motivo_desviacion' => 'Motivo de la cantidad diferente a la recomendada'] as $key => $label)<div><dt class="font-semibold dark:text-slate-100">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line text-slate-600 dark:text-slate-300">{{ $protocolo[$key] ?? 'No corresponde o no se registró' }}</dd></div>@endforeach</dl>@else<p class="text-sm text-slate-500">Muestreo anterior al registro del protocolo. Su información original se conserva.</p>@endif
        </x-ui.card>
        <x-ui.card title="Frutos de la muestra" description="Usa estos códigos como etiquetas físicas y conserva la identidad durante las inspecciones.">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@forelse($frutos as $fruto)<div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"><p class="font-mono text-sm font-semibold">{{ $fruto->codigo }}</p><p class="mt-1 text-xs text-slate-500">Fruto {{ $fruto->numero }} · {{ $fruto->evidencias_count }} fotografías</p></div>@empty<p class="text-sm text-slate-500">Muestreo histórico sin identificación individual de frutos.</p>@endforelse</div><div class="mt-4">{{ $frutos->links() }}</div>
        </x-ui.card>
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.card title="Datos del lote"><div class="space-y-3 text-sm text-slate-700 dark:text-slate-200">
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Código:</span> {{ $muestreo->lote?->codigo_lote }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Productor:</span> {{ $muestreo->lote?->productor?->nombre_razon_social ?? 'No registrado' }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Estado:</span> <x-ui.state-badge :state="$muestreo->lote?->estado" /></p>
            </div></x-ui.card>
            <x-ui.card title="Datos del muestreo"><div class="space-y-3 text-sm text-slate-700 dark:text-slate-200">
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Responsable:</span> {{ $muestreo->inspector?->nombre_completo }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Estado:</span> <x-ui.state-badge :state="$muestreo->estado" /></p>
                <p><span class="font-semibold text-slate-900 dark:text-slate-100">Observaciones:</span> {{ $muestreo->observaciones ?: 'Sin observaciones' }}</p>
            </div></x-ui.card>
        </div>
        <x-ui.card title="Inspecciones del muestreo" description="Las reinspecciones conservan los resultados anteriores.">
            <div class="space-y-3">
                @forelse($muestreo->inspecciones->sortByDesc('id_inspeccion') as $insp)
                    <a wire:navigate href="{{ route('inspecciones.show', $insp->id_inspeccion) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-4 hover:border-brand-300 dark:border-slate-700">
                        <div class="flex items-center gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-ui.icon name="camera" class="h-4 w-4" /></span><div><p class="font-semibold text-slate-900 dark:text-slate-100">{{ $insp->solicitada_por ? 'Reinspección' : 'Inspección inicial' }} #{{ $insp->id_inspeccion }}</p><p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $insp->inspector?->nombre_completo ?? 'Pendiente de asignación' }}</p></div></div>
                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 dark:text-brand-300"><x-ui.state-badge :state="$insp->estado" /> <x-ui.icon name="chevron-right" class="h-4 w-4" /></span>
                    </a>
                @empty
                    <p class="py-5 text-sm text-slate-500 dark:text-slate-400">No se han iniciado inspecciones.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
