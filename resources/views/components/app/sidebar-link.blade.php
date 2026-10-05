@props(['route', 'label', 'icon' => null, 'active' => null])

@php
    $isActive = request()->routeIs($active ?: $route);
@endphp

<a
    href="{{ route($route) }}"
    @class([
        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
        'bg-brand-50 text-brand-700' => $isActive,
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $isActive,
    ])
>
    @if ($icon)
        <x-ui.icon :name="$icon" class="h-5 w-5 shrink-0" />
    @endif
    <span>{{ $label }}</span>
</a>
