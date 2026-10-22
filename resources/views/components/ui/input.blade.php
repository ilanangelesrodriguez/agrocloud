@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
    'required' => false,
])

<div class="space-y-2">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-200">
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $type === 'password' ? '' : old($name, $value) }}"
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($required) required @endif
        {{ $attributes->class([
            'w-full rounded-xl border bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition',
            'border-slate-300 dark:border-slate-600 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50',
            'border-red-400 focus:border-red-500 focus:ring-red-100 dark:focus:ring-red-900/50' => $errors->has($name),
        ]) }}
    >


</div>
