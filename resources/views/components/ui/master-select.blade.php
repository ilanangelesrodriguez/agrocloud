@props(['name', 'kind', 'label' => null, 'value' => null, 'required' => false, 'companyId' => null, 'weightOnly' => false])
@php
    $selected = (string) old($name, $value);
    $options = app(\App\Services\MaestroCatalog::class)->options($kind, $companyId ?? auth()->user()?->id_empresa);
    if ($weightOnly) $options = $options->whereIn('codigo', ['kg', 'g', 't']);
    $scope = config('maestros.'.$kind.'.scope');
    $user = auth()->user();
    $canCreate = !$weightOnly && $scope !== 'readonly' && (($user?->isSuperAdmin() && in_array($scope, ['global', 'shared'])) || ($user?->isCompanyAdmin() && $user->id_empresa && in_array($scope, ['company', 'shared'])));
@endphp
<div class="min-w-0 space-y-2">
    @if($label)<label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label }}</label>@endif
    <select name="{{ $name }}" id="{{ $name }}" data-master-kind="{{ $kind }}" @if($required) required @endif {{ $attributes->class('min-w-0 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50') }}>
        <option value="">{{ $required ? 'Selecciona una opción' : 'Sin especificar' }}</option>
        @if($selected && !$options->contains('nombre', $selected))<option value="{{ $selected }}" selected data-master-legacy>{{ $selected }} (guardado)</option>@endif
        @foreach($options as $option)<option value="{{ $option->nombre }}" data-parent="{{ $option->parent?->nombre }}" @selected($selected === $option->nombre)>{{ $option->nombre }}</option>@endforeach
    </select>
    @error($name)<p class="text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
    @if($canCreate)
        <button type="button" data-master-create data-master-target="{{ $name }}" data-master-url="{{ route('maestros.store-option', $kind) }}" data-master-title="{{ config('maestros.'.$kind.'.label') }}" data-master-scope="{{ $user->isSuperAdmin() ? 'global' : 'company' }}" class="inline-flex items-center gap-1.5 rounded-lg py-1 text-xs font-semibold text-brand-700 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600 dark:text-brand-300"><x-ui.icon name="plus" class="h-3.5 w-3.5" />¿No encuentras la opción? Agregar nueva</button>
    @elseif($options->isEmpty())<p class="text-xs text-slate-500 dark:text-slate-400">No hay opciones disponibles. Consulta al administrador para configurar este catálogo.</p>@endif
</div>
