<x-layouts.guest title="Iniciar sesión">
    <div class="mb-7 lg:hidden">
        <div class="inline-flex items-center gap-3">
            <img src="{{ asset('images/agrocloud/logo-mark.png') }}" alt="AgroCloud AI" width="40" height="40" class="h-10 w-10 rounded-xl object-contain">
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

    @if(session('status'))<p role="status" class="mb-5 rounded-xl bg-brand-50 p-4 text-sm text-brand-800 dark:bg-brand-950 dark:text-brand-200">{{ session('status') }}</p>@endif
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

        <div class="relative">
            <x-ui.input name="password" label="Contraseña" type="password" autocomplete="current-password" placeholder="Ingresa tu contraseña" required class="pr-14" />
            <button type="button" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Mostrar contraseña" title="Mostrar contraseña" class="absolute top-7 right-1 z-10 inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-brand-300">
                <span data-password-icon-show><x-ui.icon name="eye" class="h-5 w-5" /></span>
                <span data-password-icon-hide class="hidden"><x-ui.icon name="eye-off" class="h-5 w-5" /></span>
            </button>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500">
            Mantener sesión iniciada
        </label>

        <x-ui.button type="submit" class="w-full">
            Iniciar sesión
        </x-ui.button>
    </form>

    <p class="mt-5 text-center text-sm"><a href="{{ route('password.request') }}" class="font-semibold text-brand-700 dark:text-brand-300 hover:underline">¿Olvidaste tu contraseña?</a></p>
    <p class="mt-6 text-center text-xs leading-5 text-slate-400">
        El acceso está limitado a usuarios activos de AgroCloud AI.
    </p>
</x-layouts.guest>
