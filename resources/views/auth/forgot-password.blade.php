<x-layouts.guest title="Recuperar contraseña">
    <h1 class="text-2xl font-semibold text-slate-950 dark:text-slate-100">Recuperar contraseña</h1>
    <p class="mt-2 text-sm text-slate-500">Ingresa tu correo registrado. El enlace estará disponible durante 60 minutos y se podrá usar una sola vez.</p>
    @if(session('status'))<p role="status" class="mt-5 rounded-xl bg-brand-50 p-4 text-sm text-brand-800 dark:bg-brand-950 dark:text-brand-200">{{ session('status') }}</p>@endif
    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf
        <x-ui.input name="correo" label="Correo electrónico" type="email" autocomplete="email" required autofocus />
        <x-ui.button type="submit" class="w-full">Enviar enlace</x-ui.button>
    </form>
    <a href="{{ route('login') }}" class="mt-5 inline-block text-sm font-semibold text-brand-700 dark:text-brand-300 hover:underline">Volver al inicio de sesión</a>
</x-layouts.guest>
