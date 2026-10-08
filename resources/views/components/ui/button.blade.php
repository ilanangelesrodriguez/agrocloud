@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-slate-200 dark:focus:ring-slate-700',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-200 dark:focus:ring-red-900/50',
        default => 'bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-200 dark:focus:ring-brand-900/50',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class("inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:opacity-60 {$classes}") }}
>
    {{ $slot }}
</button>
