@props(['steps', 'active' => 1, 'completed' => 0, 'icons' => []])
<ol {{ $attributes->class('grid gap-2 sm:grid-flow-col sm:auto-cols-fr') }} aria-label="Etapas del proceso">@foreach($steps as $step)
    @php
        $done = $loop->iteration <= (int) $completed;
        $current = $loop->iteration === (int) $active;
    @endphp
    <li @class(['flex items-center gap-3 rounded-xl border px-3 py-2 text-sm', 'border-brand-300 bg-brand-50 text-brand-800 dark:border-brand-700 dark:bg-brand-950/40 dark:text-brand-200' => $current || $done, 'border-slate-200 text-slate-500 dark:border-slate-700 dark:text-slate-400' => !$current && !$done]) @if($current) aria-current="step" @endif><x-ui.icon :name="$done ? 'check' : ($icons[$loop->index] ?? 'clipboard-list')" class="h-5 w-5 shrink-0" /><span><span class="block font-medium">{{ $step }}</span><span class="block text-xs">{{ $done ? 'Completado' : ($current ? 'Etapa actual' : 'Pendiente') }}</span></span></li>
@endforeach</ol>
