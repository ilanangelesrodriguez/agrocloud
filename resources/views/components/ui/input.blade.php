@props([
    'name',
    'id' => null,
    'label' => null,
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
    'required' => false,
])

<div class="min-w-0 space-y-2">
    @if ($label)
        <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-slate-700 dark:text-slate-200">
            {{ $label }}
        </label>
    @endif

    @if ($type === 'password')
        <div class="relative">
    @endif
    <input
        id="{{ $id ?? $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $type === 'password' ? '' : old($name, $value) }}"
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($required) required @endif
        @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $id ?? $name }}-error" data-field-error-id="{{ $id ?? $name }}-error" @endif
        {{ $attributes->class([
            'min-w-0 w-full rounded-xl border bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 outline-none transition',
            'border-slate-300 dark:border-slate-600 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-900/50',
            'pr-14' => $type === 'password',
        ]) }}
    >
    @if ($type === 'password')
            <button type="button" data-password-toggle aria-controls="{{ $id ?? $name }}" aria-pressed="false" aria-label="Mostrar contraseña" title="Mostrar contraseña" class="absolute inset-y-0 right-1 inline-flex w-10 cursor-pointer items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-brand-300">
                <span data-password-icon-show><x-ui.icon name="eye" class="h-5 w-5" /></span>
                <span data-password-icon-hide class="hidden"><x-ui.icon name="eye-off" class="h-5 w-5" /></span>
            </button>
        </div>
    @endif
    @error($name)<p id="{{ $id ?? $name }}-error" data-form-field-error="{{ $name }}" class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
</div>
