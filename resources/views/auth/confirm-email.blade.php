<x-layouts.guest title="Confirmar nuevo correo">
    <h1 class="text-2xl font-semibold">Confirma tu nuevo correo</h1>
    <p class="mt-3">{{ $usuario->pending_email }}</p>
    <p class="mt-3 text-sm">Esta dirección sustituirá tu correo de acceso. Se cerrarán las sesiones anteriores y se enviará un aviso al correo anterior.</p>
    <form method="POST" action="{{ route('users.email.confirm.store', ['usuario' => $usuario, 'token' => $token]) }}" class="mt-6">@csrf<x-ui.button type="submit">Confirmar cambio de correo</x-ui.button></form>
</x-layouts.guest>
