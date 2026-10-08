@props([
    'active' => false,
    'activeLabel' => 'Activo',
    'inactiveLabel' => 'Inactivo',
])

<span @class([
    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold',
    'bg-emerald-50 dark:bg-emerald-950/35 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-100 dark:ring-emerald-900/50' => $active,
    'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-700' => ! $active,
])>
    <span @class(['h-1.5 w-1.5 rounded-full', 'bg-emerald-500' => $active, 'bg-slate-400' => ! $active])></span>
    {{ $active ? $activeLabel : $inactiveLabel }}
</span>
