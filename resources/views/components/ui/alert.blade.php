@props(['type' => 'info'])

@php
    $classes = match ($type) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'error' => 'border-red-200 bg-red-50 text-red-800',
        default => 'border-sky-200 bg-sky-50 text-sky-800',
    };
@endphp

<div {{ $attributes->class("rounded-xl border px-4 py-3 text-sm {$classes}") }}>
    {{ $slot }}
</div>
