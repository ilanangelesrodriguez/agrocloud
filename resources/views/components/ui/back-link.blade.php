@props(['href', 'label' => 'Regresar'])

<a href="{{ $href }}" {{ $attributes->class('inline-flex items-center gap-2 rounded-lg px-1 py-1 text-sm font-semibold text-slate-600 dark:text-slate-300 transition hover:text-brand-700 dark:hover:text-brand-300') }}>
    <x-ui.icon name="arrow-left" class="h-4 w-4" />
    <span>{{ $label }}</span>
</a>
