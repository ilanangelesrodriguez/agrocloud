@props(['endpoint', 'nameField' => 'razon_social', 'ruc' => '', 'name' => '', 'method' => 'GET', 'help' => 'Completa el RUC para consultar la razón social.'])
<div data-ruc-lookup data-endpoint="{{ $endpoint }}" data-method="{{ $method }}" data-name-field="{{ $nameField }}" data-ruc-value="{{ $ruc }}" data-name-value="{{ $name }}" class="space-y-2">
    <button type="button" data-ruc-query class="inline-flex min-h-9 items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:cursor-wait disabled:opacity-60 dark:text-brand-300 dark:hover:bg-brand-950/40">
        <x-ui.icon name="search" class="h-4 w-4" /><span data-ruc-label>{{ $ruc ? 'Consultar RUC' : 'Completar con RUC' }}</span>
    </button>
    <p data-ruc-status role="status" aria-live="polite" class="text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $help }}</p>
    <button type="button" data-ruc-apply hidden class="rounded-lg text-xs font-semibold text-brand-700 underline decoration-brand-300 underline-offset-4 focus-visible:outline-2 focus-visible:outline-brand-600 dark:text-brand-300">Usar razón social encontrada</button>
</div>
