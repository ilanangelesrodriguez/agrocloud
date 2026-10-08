@props(['title' => 'AgroCloud AI'])

<!DOCTYPE html>
<html lang="es" data-theme-user="{{ auth()->id() }}">
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
    <meta name="empresa-id" content="{{ auth()->user()?->id_empresa }}">
    @if (config('broadcasting.default') === 'reverb')
        <meta name="reverb-key" content="{{ config('broadcasting.connections.reverb.key') }}">
        <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.client.host') }}">
        <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.client.port') }}">
        <meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.client.scheme') }}">
    @endif
    <title>{{ $title }} · AgroCloud AI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 antialiased">
    @php
        $currentUser = auth()->user()?->loadMissing(['rol', 'empresa']);
        $lastSection = null;
    @endphp

    <div class="agrocloud-shell min-h-screen lg:grid" data-sidebar-collapsed="false">
        <aside
            id="app-sidebar"
            aria-label="Navegación principal"
            class="agrocloud-sidebar fixed inset-y-0 left-0 z-50 flex h-dvh w-[264px] -translate-x-full flex-col overflow-hidden border-r border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xl invisible transition-[transform,visibility] duration-300 ease-out motion-reduce:transition-none lg:sticky lg:inset-y-auto lg:top-0 lg:z-20 lg:h-dvh lg:translate-x-0 lg:self-start lg:visible lg:shadow-none"
        >
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-100 dark:border-slate-700 px-4">
                <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-600 text-sm font-bold text-white">AC</div>
                <div data-sidebar-brand-text class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">AgroCloud AI</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $currentUser?->empresa?->nombre_comercial ?? 'Administración global' }}</p>
                </div>
                <button
                    type="button"
                    data-sidebar-close
                    aria-label="Cerrar menú"
                    class="ml-auto inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 dark:text-slate-300 transition hover:bg-slate-100 dark:hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 lg:hidden"
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path d="M18 6 6 18M6 6l12 12" stroke-linecap="round"/></svg>
                </button>
            </div>

            <nav aria-label="Secciones" class="agrocloud-sidebar-nav min-h-0 flex-1 overflow-y-auto overscroll-contain p-3">
                @foreach (config('navigation.sidebar', []) as $item)
                    @php
                        $permission = $item['permission'] ?? null;
                        $superadminOnly = $item['superadmin'] ?? false;
                        $requiresCompany = $item['requires_company'] ?? false;
                    @endphp
                    @continue($superadminOnly && ! $currentUser?->isSuperAdmin())
                    @continue($requiresCompany && ! $currentUser?->empresa)
                    @continue($permission && ! $currentUser?->hasPermission($permission))

                    @if (($item['section'] ?? null) !== $lastSection)
                        @php($lastSection = $item['section'] ?? null)
                        @if ($lastSection)
                            <p data-sidebar-section class="mb-2 mt-5 px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 first:mt-1">{{ $lastSection }}</p>
                        @endif
                    @endif

                    <x-app.sidebar-link
                        :route="$item['route']"
                        :active="$item['active'] ?? null"
                        :label="$item['label']"
                        :icon="$item['icon'] ?? null"
                    />
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-700 bg-white/95 dark:bg-slate-900/95 px-4 backdrop-blur sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        data-sidebar-toggle
                        aria-label="Abrir menú de navegación"
                        aria-controls="app-sidebar"
                        aria-expanded="false"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 lg:hidden"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <button
                        type="button"
                        data-sidebar-collapse-toggle
                        aria-label="Contraer menú lateral"
                        aria-controls="app-sidebar"
                        aria-pressed="false"
                        title="Contraer menú lateral"
                        class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 lg:inline-flex"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16"/></svg>
                    </button>
                    <div class="min-w-0">
                        @if ($currentUser?->hasPermission('dashboard.ver') && ! request()->routeIs(\App\Constants\RouteNames::DASHBOARD))
                            <nav aria-label="Ruta de navegación" class="hidden min-w-0 items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 sm:flex">
                                <a href="{{ route(\App\Constants\RouteNames::DASHBOARD) }}" class="shrink-0 hover:text-brand-700 dark:hover:text-brand-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600">Inicio</a>
                                <span aria-hidden="true">/</span>
                                <span aria-current="page" class="truncate">{{ $title }}</span>
                            </nav>
                        @endif
                        <h1 class="truncate text-base font-semibold text-slate-900 dark:text-slate-100 sm:text-lg">{{ $title }}</h1>
                        @if ($currentUser?->empresa)
                            <p class="hidden truncate text-xs text-slate-400 sm:block">Trabajando en {{ $currentUser->empresa->nombre_comercial }}</p>
                        @endif
                    </div>
                </div>

                <div class="relative shrink-0" data-user-menu>
                    <button type="button" data-user-menu-toggle aria-controls="user-menu-panel" class="flex items-center gap-3 rounded-xl px-2 py-1.5 text-left transition hover:bg-slate-50 dark:hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600" aria-expanded="false">
                        @if ($currentUser?->foto_perfil_url)
                            <img src="{{ $currentUser->foto_perfil_url }}" alt="Foto de perfil" class="h-9 w-9 rounded-full object-cover ring-2 ring-slate-100 dark:ring-slate-700">
                        @else
                            <div class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                {{ strtoupper(substr($currentUser?->nombre ?? 'U', 0, 1)) }}{{ strtoupper(substr($currentUser?->apellido ?? '', 0, 1)) }}
                            </div>
                        @endif
                        <div class="hidden min-w-0 text-right sm:block">
                            <p class="max-w-44 truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $currentUser?->nombre_completo }}</p>
                            <p class="max-w-44 truncate text-xs text-slate-500 dark:text-slate-400">{{ $currentUser?->rol?->nombre ?? 'Sin rol' }}</p>
                        </div>
                        <x-ui.icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                    </button>

                    <div id="user-menu-panel" data-user-menu-panel class="absolute right-0 mt-2 hidden w-64 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg">
                        <div class="border-b border-slate-100 dark:border-slate-700 px-4 py-3">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $currentUser?->nombre_completo }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $currentUser?->correo }}</p>
                            @if ($currentUser?->empresa)
                                <p class="mt-1 truncate text-xs font-medium text-brand-700 dark:text-brand-300">{{ $currentUser->empresa->nombre_comercial }}</p>
                            @endif
                        </div>

                        <div class="p-2">
                            <a href="{{ route(\App\Constants\RouteNames::PROFILE) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600">
                                <x-ui.icon name="user" class="h-4 w-4" /> Mi perfil
                            </a>
                            @if ($currentUser?->empresa && $currentUser->hasPermission('empresa.ver'))
                                <a href="{{ route(\App\Constants\RouteNames::EMPRESA) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600">
                                    <x-ui.icon name="building" class="h-4 w-4" /> Mi empresa
                                </a>
                            @endif
                        </div>
                        {{-- Apariencia: preferencia en este navegador para la cuenta actual. --}}
                        <div class="border-t border-slate-100 dark:border-slate-700 px-2 py-2" aria-label="Apariencia">
                            <p class="px-3 pb-1.5 pt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Apariencia <span class="normal-case font-normal" data-theme-current>Sistema</span></p>
                            <button type="button" data-theme-option="light" aria-pressed="false" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600"><x-ui.icon name="sun" class="h-4 w-4" /> Claro</button>
                            <button type="button" data-theme-option="dark" aria-pressed="false" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600"><x-ui.icon name="moon" class="h-4 w-4" /> Oscuro</button>
                            <button type="button" data-theme-option="system" aria-pressed="false" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600"><x-ui.icon name="monitor" class="h-4 w-4" /> Sistema</button>
                        </div>



                        <form method="POST" action="{{ route(\App\Constants\RouteNames::LOGOUT) }}" class="border-t border-slate-100 dark:border-slate-700 p-2">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-red-600 dark:hover:text-red-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600">
                                <x-ui.icon name="logout" class="h-4 w-4" /> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <div class="mx-auto max-w-7xl">
                    @if (session('success'))
                        <div class="mb-5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/35 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-200">{{ session('success') }}</div>
                    @endif
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <div data-sidebar-backdrop aria-hidden="true" class="pointer-events-none invisible fixed inset-0 z-40 bg-slate-950/40 opacity-0 transition-[opacity,visibility] duration-300 ease-out motion-reduce:transition-none lg:hidden"></div>
</body>
</html>
