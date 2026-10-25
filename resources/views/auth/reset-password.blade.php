<x-layouts.guest title="Restablecer contraseña">
    <h1 class="text-2xl font-semibold text-slate-950 dark:text-slate-100">Restablecer contraseña</h1>
    <p class="mt-2 text-sm text-slate-500">Usa al menos 12 caracteres, con mayúsculas, minúsculas y números.</p>
    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-ui.input name="correo" label="Correo electrónico" type="email" :value="old('correo', $correo)" autocomplete="email" required />
        <x-ui.input name="password" label="Nueva contraseña" type="password" autocomplete="new-password" minlength="12" maxlength="255" required />
        <x-ui.input name="password_confirmation" label="Confirmar contraseña" type="password" autocomplete="new-password" minlength="12" maxlength="255" required />
        <x-ui.button type="submit" class="w-full">Guardar contraseña</x-ui.button>
    </form>
    <a href="{{ route('password.request') }}" class="mt-5 inline-block text-sm font-semibold text-brand-700 dark:text-brand-300 hover:underline">Solicitar otro enlace</a>
</x-layouts.guest>
