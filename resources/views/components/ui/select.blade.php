@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'placeholder' => 'Selecciona una opción',
])

<div class="space-y-2">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->class([
            'w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition',
            'border-slate-300 focus:border-brand-500 focus:ring-4 focus:ring-brand-100',
            'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has($name),
        ]) }}
    >
        <option value="">{{ $placeholder }}</option>
        {{ $slot }}
    </select>

    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
