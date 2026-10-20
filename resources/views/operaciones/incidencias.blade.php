<x-layouts.app title="Incidencias operativas">
    @php
        $rol = auth()->user()->rol?->codigo;
        $supervisor = $rol === 'SUPERVISOR';
        $puedeCrear = in_array($rol, ['ADMIN', 'RECEPCION', 'INSPECTOR', 'SUPERVISOR'], true);
    @endphp
    <div class="space-y-6">
        <x-ui.back-link :href="$lote ? route('lotes.show', $lote->id_lote) : route('dashboard')" :label="$lote ? 'Volver al lote' : 'Volver al dashboard'" />
        <div><h1 class="text-2xl font-semibold dark:text-slate-100">Incidencias {{ $lote ? '· '.$lote->codigo_lote : 'operativas' }}</h1><p class="mt-2 text-sm text-slate-500">Registra el problema, documenta la corrección y solicita la verificación del supervisor. Las incidencias altas y críticas impiden liberar el lote hasta su cierre.</p></div>
        @if(!$lote)
            <x-ui.card title="Registrar una incidencia"><p class="text-sm text-slate-600 dark:text-slate-300">Abre el lote afectado y selecciona «Gestionar incidencias del lote» para mantener la relación con su expediente.</p><a wire:navigate href="{{ route('lotes.index') }}" class="mt-3 inline-flex text-sm font-semibold text-brand-700 dark:text-brand-300">Seleccionar un lote <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></x-ui.card>
        @elseif($puedeCrear)
            <x-ui.card title="Nueva incidencia" description="Un hallazgo describe lo observado; una incidencia requiere un responsable y una acción de seguimiento.">
                <form data-ajax-form action="{{ route('incidencias.store', $lote->id_lote) }}" method="POST" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <label class="text-sm font-medium dark:text-slate-200">Problema *<input name="titulo" required maxlength="150" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"></label>
                    <label class="text-sm font-medium dark:text-slate-200">Gravedad *<select name="gravedad" required class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"><option value="">Selecciona la gravedad</option>@foreach(['BAJA' => 'Baja', 'MEDIA' => 'Media', 'ALTA' => 'Alta · bloquea liberación', 'CRITICA' => 'Crítica · bloquea liberación'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                    <label class="text-sm font-medium dark:text-slate-200">Responsable *<select name="responsable" required class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"><option value="">Selecciona al responsable</option>@foreach($responsables as $usuario)<option value="{{ $usuario->id_usuario }}">{{ $usuario->nombre_completo }}</option>@endforeach</select></label>
                    <label class="text-sm font-medium dark:text-slate-200">Descripción *<textarea name="descripcion" required maxlength="3000" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"></textarea></label>
                    <div class="md:col-span-2"><x-ui.button type="submit">Registrar y notificar</x-ui.button></div>
                </form>
            </x-ui.card>
        @endif
        <div class="space-y-4">
            @forelse($incidencias as $incidencia)
                @php
                    $responsable = $responsables->firstWhere('id_usuario', $incidencia->responsable);
                    $puedeAtender = $supervisor || (int) $incidencia->responsable === (int) auth()->id();
                    $transiciones = match ($incidencia->estado) {
                        'ABIERTA' => ['EN_ATENCION' => 'Iniciar atención'],
                        'EN_ATENCION' => ['RESUELTA' => 'Marcar como resuelta'],
                        'RESUELTA' => $supervisor ? ['CERRADA' => 'Verificar y cerrar', 'EN_ATENCION' => 'Solicitar corrección adicional'] : [],
                        'CERRADA' => $supervisor ? ['ABIERTA' => 'Reabrir incidencia'] : [], default => [],
                    };
                @endphp
                <x-ui.card :title="'#'.$incidencia->id.' · '.$incidencia->titulo">
                    <div class="flex flex-wrap items-center gap-3 text-sm"><x-ui.state-badge :state="$incidencia->estado" /><x-ui.state-badge :state="$incidencia->gravedad" /><a wire:navigate class="font-semibold text-brand-700 dark:text-brand-300" href="{{ route('lotes.show', $incidencia->id_lote) }}">{{ $lotes->get($incidencia->id_lote)?->codigo_lote ?? 'Ver lote' }}</a><span class="text-slate-500">Responsable: {{ $responsable?->nombre_completo ?? 'Usuario inactivo' }}</span></div>
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">{{ $incidencia->descripcion }}</p>
                    <details class="mt-4"><summary class="cursor-pointer text-sm font-semibold text-brand-700 dark:text-brand-300">Historial de seguimiento</summary><ol class="mt-3 space-y-3">@foreach($incidencia->historial as $evento)@php
                        $datos = json_decode($evento->cambios, true) ?? [];
                    @endphp<li class="border-l-2 border-brand-300 pl-3 text-sm dark:text-slate-200"><p class="font-semibold">{{ str_replace('_', ' ', $datos['estado'] ?? 'ABIERTA') }} · {{ \Carbon\Carbon::parse($evento->created_at)->format('d/m/Y H:i') }}</p><p class="mt-1 whitespace-pre-line">{{ $datos['accion'] ?? $datos['descripcion'] ?? '' }}</p><p class="mt-1 whitespace-pre-line text-slate-500">{{ $datos['comprobacion'] ?? '' }}</p>@if(!empty($datos['evidencia']))<p class="mt-1 text-xs">Fotografía del expediente #{{ $datos['evidencia'] }}</p>@endif</li>@endforeach</ol></details>
                    @if($puedeAtender && $transiciones)
                        <form data-ajax-form method="POST" action="{{ route('incidencias.update', $incidencia->id) }}" class="mt-5 grid gap-3 border-t border-slate-200 pt-4 dark:border-slate-700 md:grid-cols-2">
                            @csrf
                            <label class="text-sm font-medium dark:text-slate-200">Siguiente paso *<select name="estado" required class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900">@foreach($transiciones as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                            <label class="text-sm font-medium dark:text-slate-200">Acción realizada *<textarea name="accion" required maxlength="3000" rows="2" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"></textarea></label>
                            <label class="text-sm font-medium dark:text-slate-200">Comprobación de la corrección<textarea name="comprobacion" maxlength="1500" rows="2" @required(in_array($incidencia->estado, ['EN_ATENCION', 'RESUELTA'], true)) placeholder="Obligatoria al resolver o cerrar: explica cómo se comprobó la corrección" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"></textarea></label>
                            @if($lote)<label class="text-sm font-medium dark:text-slate-200">Fotografía de respaldo (opcional)<select name="evidencia" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"><option value="">Sin fotografía adicional</option>@foreach($evidencias as $foto)<option value="{{ $foto->id_evidencia }}">Evidencia #{{ $foto->id_evidencia }}</option>@endforeach</select></label>@endif
                            <div class="md:col-span-2"><x-ui.button type="submit">Guardar seguimiento</x-ui.button></div>
                        </form>
                    @endif
                </x-ui.card>
            @empty
                <x-ui.card title="Sin incidencias registradas"><p class="text-sm text-slate-500">Las incidencias aparecerán aquí con su responsable y seguimiento.</p></x-ui.card>
            @endforelse
        </div>
        {{ $eventos->links() }}
    </div>
</x-layouts.app>
