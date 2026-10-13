@props(['route', 'label', 'icon' => null, 'active' => null])

@php
    $isActive = request()->routeIs($active ?: $route);
@endphp

<a
    href="{{ route($route) }}"
    wire:navigate
    aria-label="{{ $label }}"
    title="{{ $label }}"
    @if ($isActive) aria-current="page" @endif
    @class([
        'agrocloud-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600',
        'bg-brand-50 dark:bg-brand-900/35 text-brand-700 dark:text-brand-300' => $isActive,
        'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-slate-100' => ! $isActive,
    ])
>
    @if ($icon)
        <x-ui.icon :name="$icon" class="h-5 w-5 shrink-0" />
    @endif
    <span data-sidebar-label>{{ $label }}</span>
</a>
