@props(['type' => 'info'])

@php
    $classes = match ($type) {
        'success' => 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/35 text-emerald-800 dark:text-emerald-200',
        'error' => 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/35 text-red-800 dark:text-red-200',
        default => 'border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-950/35 text-sky-800 dark:text-sky-200',
    };
@endphp

<div {{ $attributes->class("rounded-xl border px-4 py-3 text-sm {$classes}") }}>
    {{ $slot }}
</div>
