@props(['title' => 'AgroCloud AI', 'landing' => false, 'description' => null])

<!DOCTYPE html>
<html lang="es">
<head>
    {{-- Tema aplicado antes del CSS para evitar parpadeo blanco al cargar. --}}
    <script data-agrocloud-theme-init>
        (function () {
            try {
                var storageKey = 'agrocloud.theme.' + (document.documentElement.dataset.themeUser || 'guest');
                var preference = localStorage.getItem(storageKey);
                var isDark = preference === 'dark' || ((preference === 'system' || !preference) && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
            } catch (error) {
                document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches);
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · AgroCloud AI</title>
    @if($description)<meta name="description" content="{{ $description }}">@endif
    @if($landing && request()->is('/'))
        <link rel="canonical" href="{{ url('/') }}">
        <meta property="og:locale" content="es_PE">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="AgroCloud AI">
        <meta property="og:title" content="{{ $title }} · AgroCloud AI">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:image" content="{{ asset('images/agrocloud/logo-mark.png') }}">
        <meta property="og:image:alt" content="AgroCloud AI">
        <meta name="twitter:card" content="summary">
    @endif
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 antialiased">
    @if($landing)
        {{ $slot }}
    @else
    <header class="fixed inset-x-0 top-0 z-40 border-b border-slate-200/80 bg-slate-50/95 pt-[env(safe-area-inset-top)] backdrop-blur dark:border-slate-700 dark:bg-slate-800/95 lg:left-[52.5%]">
        <nav aria-label="Navegación de acceso" class="flex h-18 items-center justify-between gap-4 px-5 sm:px-8 lg:px-12">
            <a wire:navigate href="{{ url('/') }}" class="inline-flex min-h-11 items-center gap-2 rounded-lg text-sm font-medium text-brand-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600 dark:text-brand-300"><x-ui.icon name="arrow-left" class="h-4 w-4 shrink-0" />Volver al inicio</a>
            <button type="button" data-theme-switch aria-label="Cambiar apariencia" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600 dark:border-slate-700 dark:hover:bg-slate-700"><x-ui.icon name="sun" class="hidden h-5 w-5 dark:block" /><x-ui.icon name="moon" class="h-5 w-5 dark:hidden" /></button>
        </nav>
    </header>
    <main class="grid min-h-dvh lg:grid-cols-[1.05fr_0.95fr]">
        <section class="hidden bg-brand-700 p-10 text-white lg:flex lg:flex-col lg:justify-between xl:p-14">
            <div>
                <div class="inline-flex items-center gap-3">
                    <img src="{{ asset('images/agrocloud/logo-mark.png') }}" alt="AgroCloud AI" width="44" height="44" class="h-11 w-11 rounded-2xl object-contain">
                    <div>
                        <p class="text-lg font-semibold">AgroCloud AI</p>
                        <p class="text-sm text-brand-100">Gestión inteligente de Pre-Empaque</p>
                    </div>
                </div>
            </div>

            <div class="max-w-xl">
                <p class="text-sm font-medium uppercase tracking-[0.18em] text-brand-200">Plataforma agrícola</p>
                <h1 class="mt-4 text-4xl font-semibold leading-tight xl:text-5xl">
                    Control de calidad, trazabilidad e inteligencia artificial en un solo flujo.
                </h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-brand-100">
                    Base preparada para recepción de lotes, inspecciones, evidencias y detección de plagas.
                </p>
            </div>

            <p class="text-sm text-brand-200">AgroCloud AI · {{ now()->year }}</p>
        </section>

        <section class="flex items-start justify-center px-5 pb-8 pt-[calc(6rem+env(safe-area-inset-top))] sm:items-center sm:px-8 sm:pb-12 lg:px-12 lg:pt-28">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </section>
    </main>
    @endif
    <x-app.feedback />
    @livewireScripts
</body>
</html>
