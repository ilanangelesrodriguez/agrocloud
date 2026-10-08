@props(['route', 'label', 'icon' => null, 'active' => null])

@php
    $isActive = request()->routeIs($active ?: $route);
@endphp

<a
    href="{{ route($route) }}"
    aria-label="{{ $label }}"
    title="{{ $label }}"
    @if ($isActive) aria-current="page" @endif
    @class([
        'agrocloud-sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600',
        'bg-brand-50 text-brand-700' => $isActive,
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $isActive,
    ])
>
    @if ($icon)
        <x-ui.icon :name="$icon" class="h-5 w-5 shrink-0" />
    @endif
    <span data-sidebar-label>{{ $label }}</span>
</a>
