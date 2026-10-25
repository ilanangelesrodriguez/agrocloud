@props(['lote', 'muestreo' => null, 'inspeccion' => null])
<nav aria-label="Contexto del expediente" class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
@if(auth()->user()->hasPermission('lotes.ver'))<a wire:navigate href="{{ route('lotes.show', $lote->id_lote).'#lote-control' }}" class="inline-flex items-center gap-1 font-medium text-brand-700 dark:text-brand-300"><x-ui.icon name="briefcase" class="h-4 w-4" />{{ $lote->codigo_lote }}</a>@else<span>{{ $lote->codigo_lote }}</span>@endif
@if($muestreo)<x-ui.icon name="chevron-right" class="h-3.5 w-3.5" />@if(auth()->user()->hasPermission('muestreos.ver'))<a wire:navigate href="{{ route('muestreos.show', $muestreo->id_muestreo) }}" class="font-medium text-brand-700 dark:text-brand-300">Muestreo #{{ $muestreo->id_muestreo }}</a>@else<span>Muestreo #{{ $muestreo->id_muestreo }}</span>@endif @endif
@if($inspeccion)<x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /><span>Inspección #{{ $inspeccion->id_inspeccion }}</span>@endif
</nav>
