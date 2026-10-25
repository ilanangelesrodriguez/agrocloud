<x-layouts.guest title="Activar cuenta">
    <h1 class="text-2xl font-semibold">Activa tu cuenta</h1>
    <p class="mt-2 text-sm">{{ $usuario->correo }} · {{ $usuario->empresa?->nombre_comercial }}</p>
    <p class="mt-2 text-sm">Crea una contraseña de al menos 12 caracteres, con mayúsculas, minúsculas y números.</p>
    <form method="POST" action="{{ route('users.invitation.activate', ['usuario' => $usuario, 'token' => $token]) }}" class="mt-6 space-y-5">
        @csrf
        <x-ui.input name="password" label="Contraseña" type="password" autocomplete="new-password" minlength="12" maxlength="255" required />
        <x-ui.input name="password_confirmation" label="Confirmar contraseña" type="password" autocomplete="new-password" minlength="12" maxlength="255" required />
        <x-ui.button type="submit" class="w-full">Activar cuenta</x-ui.button>
    </form>
</x-layouts.guest>
