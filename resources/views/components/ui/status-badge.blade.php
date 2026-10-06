@props([
    'active' => false,
    'activeLabel' => 'Activo',
    'inactiveLabel' => 'Inactivo',
])

<span @class([
    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold',
    'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' => $active,
    'bg-slate-100 text-slate-600 ring-1 ring-slate-200' => ! $active,
])>
    <span @class(['h-1.5 w-1.5 rounded-full', 'bg-emerald-500' => $active, 'bg-slate-400' => ! $active])></span>
    {{ $active ? $activeLabel : $inactiveLabel }}
</span>
