@props(['title' => 'AgroCloud AI'])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · AgroCloud AI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 hidden w-[260px] border-r border-slate-200 bg-white lg:static lg:block">
            <div class="flex h-16 items-center gap-3 border-b border-slate-100 px-5">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-sm font-bold text-white">AC</div>
                <div>
                    <p class="text-sm font-semibold text-slate-900">AgroCloud AI</p>
                    <p class="text-xs text-slate-500">Pre-Empaque</p>
                </div>
            </div>

            <nav class="space-y-1 p-3">
                @foreach (config('navigation.sidebar', []) as $item)
                    <x-app.sidebar-link
                        :route="$item['route']"
                        :label="$item['label']"
                        :icon="$item['icon'] ?? null"
                    />
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" data-sidebar-toggle class="rounded-lg border border-slate-200 p-2 text-slate-600 lg:hidden" aria-label="Abrir menú">
                        <span class="block h-0.5 w-5 bg-current"></span>
                        <span class="mt-1 block h-0.5 w-5 bg-current"></span>
                        <span class="mt-1 block h-0.5 w-5 bg-current"></span>
                    </button>
                    <h1 class="text-base font-semibold text-slate-900 sm:text-lg">{{ $title }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium text-slate-800">{{ auth()->user()?->nombre }}</p>
                        <p class="text-xs text-slate-500">{{ auth()->user()?->rol?->nombre ?? 'Sin rol' }}</p>
                    </div>
                    <form method="POST" action="{{ route(\App\Constants\RouteNames::LOGOUT) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                            <x-ui.icon name="logout" class="h-4 w-4" />
                            <span class="hidden sm:inline">Salir</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <div class="mx-auto max-w-7xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <div data-sidebar-backdrop class="fixed inset-0 z-30 hidden bg-slate-950/30 lg:hidden"></div>
</body>
</html>
