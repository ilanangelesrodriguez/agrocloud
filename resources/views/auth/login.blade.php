<x-layouts.guest title="Iniciar sesión">
    <div class="mb-7 lg:hidden">
        <div class="inline-flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 text-sm font-bold text-white">AC</div>
            <div>
                <p class="font-semibold text-slate-900 dark:text-slate-100">AgroCloud AI</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Gestión agrícola inteligente</p>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <p class="text-sm font-medium text-brand-700 dark:text-brand-300">Acceso al sistema</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 dark:text-slate-100">Bienvenido</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
            Ingresa con el correo y contraseña registrados para tu empresa.
        </p>
    </div>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
    @endif

    <form data-ajax-form method="POST" action="{{ route(\App\Constants\RouteNames::LOGIN_STORE) }}" class="space-y-5">
        @csrf

        <x-ui.input
            name="correo"
            label="Correo electrónico"
            type="email"
            autocomplete="username"
            placeholder="usuario@empresa.com"
            required
            autofocus
        />

        <x-ui.input
            name="password"
            label="Contraseña"
            type="password"
            autocomplete="current-password"
            placeholder="••••••••"
            required
        />

        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500">
            Mantener sesión iniciada
        </label>

        <x-ui.button type="submit" class="w-full">
            Iniciar sesión
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-xs leading-5 text-slate-400">
        El acceso está limitado a usuarios activos de AgroCloud AI.
    </p>
</x-layouts.guest>
