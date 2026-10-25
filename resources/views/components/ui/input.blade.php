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
        @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" data-field-error-id="{{ $name }}-error" @endif
        {{ $attributes->class([
            'w-full rounded-xl border bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition',
            'border-slate-300 dark:border-slate-600 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50',
        ]) }}
    >
    @error($name)<p id="{{ $name }}-error" data-form-field-error="{{ $name }}" class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
</div>
