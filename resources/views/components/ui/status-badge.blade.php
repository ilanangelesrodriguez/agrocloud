@props(['active' => false, 'activeLabel' => 'Activo', 'inactiveLabel' => 'Inactivo'])
<x-ui.state-badge :state="$active ? 'ACTIVO' : 'INACTIVO'" :label="$active ? $activeLabel : $inactiveLabel" {{ $attributes }} />
