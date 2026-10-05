@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-200',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-200',
        default => 'bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-200',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class("inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:opacity-60 {$classes}") }}
>
    {{ $slot }}
</button>
