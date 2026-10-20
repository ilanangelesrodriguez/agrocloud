<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95">
    <nav aria-label="Navegación pública" class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-5 py-3 sm:px-8">
        <a wire:navigate href="{{ url('/') }}" aria-label="AgroCloud AI, inicio" class="flex shrink-0 items-center gap-2 rounded-lg font-semibold focus-visible:outline-2 focus-visible:outline-brand-600"><img src="{{ asset('images/agrocloud/logo-mark.png') }}" alt="" width="36" height="36" class="h-9 w-9 object-contain"><span>AgroCloud <span class="text-brand-700 dark:text-brand-400">AI</span></span></a>
        <div class="hidden items-center gap-6 text-sm text-slate-600 xl:flex dark:text-slate-300">
            @foreach(['plataforma' => 'Plataforma', 'flujo' => 'Cómo funciona', 'inteligencia-artificial' => 'Inteligencia artificial', 'empresas' => 'Para tu empresa'] as $id => $label)<a href="{{ url('/').'#'.$id }}" class="rounded py-2 hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-brand-600 dark:hover:text-brand-300">{{ $label }}</a>@endforeach
        </div>
        <div class="flex items-center gap-1.5 sm:gap-3">
            <button type="button" data-theme-switch aria-label="Cambiar apariencia" class="grid h-11 w-11 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-brand-600 dark:text-slate-300 dark:hover:bg-slate-800"><x-ui.icon name="moon" class="h-5 w-5 dark:hidden" /><x-ui.icon name="sun" class="hidden h-5 w-5 dark:block" /></button>
            <a wire:navigate href="{{ route('login') }}" class="rounded-xl px-3 py-3 text-sm font-semibold hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-brand-600 dark:hover:bg-slate-800">Ingresar</a>
            @unless(request()->routeIs('acceso.create'))<a wire:navigate href="{{ route('acceso.create') }}" class="hidden rounded-xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 sm:inline-flex">Solicitar acceso</a>@endunless
        </div>
        <details data-public-menu class="w-full border-t border-slate-100 pt-2 xl:hidden dark:border-slate-800">
            <summary class="cursor-pointer rounded-lg px-2 py-2 text-sm font-semibold text-slate-700 focus-visible:outline-2 focus-visible:outline-brand-600 dark:text-slate-200">Explorar AgroCloud AI</summary>
            <div class="grid gap-1 py-2 sm:grid-cols-2">@foreach(['plataforma' => 'Plataforma', 'flujo' => 'Cómo funciona', 'inteligencia-artificial' => 'Inteligencia artificial', 'empresas' => 'Para tu empresa'] as $id => $label)<a href="{{ url('/').'#'.$id }}" class="rounded-xl px-3 py-3 text-sm text-slate-600 hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-brand-600 dark:text-slate-300 dark:hover:bg-brand-950">{{ $label }}</a>@endforeach
            @unless(request()->routeIs('acceso.create'))<a wire:navigate href="{{ route('acceso.create') }}" class="mt-2 rounded-xl bg-brand-700 px-3 py-3 text-center text-sm font-semibold text-white sm:hidden">Solicitar acceso empresarial</a>@endunless</div>
        </details>
    </nav>
</header>
