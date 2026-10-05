@props(['route', 'label', 'icon' => null])

@php
    $active = request()->routeIs($route);
@endphp

<a
    href="{{ route($route) }}"
    @class([
        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
        'bg-brand-50 text-brand-700' => $active,
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
    ])
>
    @if ($icon)
        <x-ui.icon :name="$icon" class="h-5 w-5 shrink-0" />
    @endif
    <span>{{ $label }}</span>
</a>
