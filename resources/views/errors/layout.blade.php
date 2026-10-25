@php
    $status = trim($__env->yieldContent('code'));
    $errorUser = in_array($status, ['500', '503'], true) ? null : request()->user();
    $destination = $errorUser ? ($errorUser->hasPermission('dashboard.ver') ? url('/dashboard') : url('/perfil')) : url('/login');
    $destinationLabel = $errorUser ? ($destination === url('/dashboard') ? 'Volver al dashboard' : 'Ir a mi perfil') : 'Iniciar sesión';
@endphp
<!DOCTYPE html>
<html lang="es" data-theme-user="{{ $errorUser?->getKey() }}" data-theme-preference="{{ $errorUser?->theme_preference ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · AgroCloud AI</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script>
        (() => {
            let preference = document.documentElement.dataset.themePreference;
            try { preference ||= localStorage.getItem('agrocloud.theme.' + (document.documentElement.dataset.themeUser || 'guest')); } catch {}
            const dark = preference === 'dark' || ((!preference || preference === 'system') && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        })();
    </script>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[data-error-page] *{box-sizing:border-box}body[data-error-page]{margin:0;background:#f7faf8;color:#142b22;font:16px/1.6 system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}[data-error-page] .shell{min-height:100vh;min-height:100dvh;display:flex;flex-direction:column}[data-error-page] .brand{display:flex;align-items:center;gap:12px;text-decoration:none;color:inherit;font-weight:650;padding:24px clamp(20px,5vw,64px)}[data-error-page] .brand img{width:40px;height:40px;object-fit:contain}[data-error-page] main{flex:1;display:grid;place-items:center;padding:24px 20px 64px}[data-error-page] .card{width:100%;max-width:600px;border:1px solid #dce7e0;border-radius:24px;background:#fff;padding:clamp(24px,5vw,48px);box-shadow:0 14px 48px #12493208}[data-error-page] .icon{display:grid;place-items:center;width:60px;height:60px;border-radius:18px;background:#effcf5;color:#146f47}[data-error-page] .icon svg{width:28px;height:28px}[data-error-page] .code{margin:24px 0 8px;color:#146f47;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}[data-error-page] h1{font-size:clamp(26px,5vw,34px);line-height:1.2;letter-spacing:-.03em;margin:0 0 16px}[data-error-page] p{margin:0}[data-error-page] .description{color:#52645b}[data-error-page] .help{margin-top:24px;padding:16px 18px;border-radius:14px;background:#f5f8f6;color:#52645b;font-size:14px}[data-error-page] .help strong{display:block;color:#263f32;margin-bottom:4px}[data-error-page] .actions{margin-top:28px;display:flex;flex-wrap:wrap;gap:12px}[data-error-page] .button{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 18px;border-radius:12px;background:#146f47;color:white;text-decoration:none;font-size:14px;font-weight:650;border:1px solid #146f47;min-height:46px}[data-error-page] .button svg{width:18px;height:18px;flex-shrink:0}[data-error-page] .button:hover{background:#14583b}[data-error-page] .button.secondary{background:transparent;border-color:#dce7e0;color:#324c3d}[data-error-page] .button.secondary:hover{background:#f5f8f6}[data-error-page] a:focus-visible{outline:3px solid #20ad6b;outline-offset:4px}[data-error-page] footer{text-align:center;color:#6c7f73;padding:0 20px 24px;font-size:12px}.dark body[data-error-page]{background:#0f172a;color:#f1f5f9}.dark [data-error-page] .card{background:#172235;border-color:#334155}.dark [data-error-page] .icon{background:#124932;color:#7de0ad}.dark [data-error-page] .code{color:#7de0ad}.dark [data-error-page] .description,.dark [data-error-page] .help{color:#bdc9d8}.dark [data-error-page] .help{background:#1e293b}.dark [data-error-page] .help strong{color:#e2e8f0}.dark [data-error-page] .button.secondary{border-color:#475569;color:#e2e8f0}.dark [data-error-page] .button.secondary:hover{background:#1e293b}.dark [data-error-page] footer{color:#94a3b8}@media(max-width:480px){[data-error-page] .actions{flex-direction:column}[data-error-page] .button{width:100%}[data-error-page] .brand{padding:20px}[data-error-page] main{padding-top:12px}}
    </style>
</head>
<body data-error-page>
    <div class="shell">
        <a wire:navigate class="brand" href="{{ url('/') }}"><img src="{{ asset('images/agrocloud/logo-mark.png') }}" alt=""><span>AgroCloud AI</span></a>
        <main>
            <section class="card" aria-labelledby="error-title">
                <div class="icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7l8-4Z"/><path d="M12 8v5m0 3v.5"/></svg></div>
                <p class="code">Error @yield('code')</p>
                <h1 id="error-title">@yield('title')</h1>
                <p class="description">@yield('description')</p>
                <div class="help"><strong>¿Qué puedes hacer?</strong>@yield('help')</div>
                <nav class="actions" aria-label="Opciones para continuar">
                    @if(in_array($status, ['500', '503'], true))
                        <a wire:navigate class="button" href="{{ url('/') }}">Ir al inicio</a>
                    @else
                        <a wire:navigate class="button" href="{{ $destination }}"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 5-7 7 7 7M5 12h14"/></svg>{{ $destinationLabel }}</a>
                        @if($errorUser && $destination !== url('/perfil'))<a wire:navigate class="button secondary" href="{{ url('/perfil') }}">Ver mi cuenta</a>@elseif(!$errorUser)<a wire:navigate class="button secondary" href="{{ url('/') }}">Volver al inicio</a>@endif
                    @endif
                </nav>
            </section>
        </main>
        <footer>AgroCloud AI · Control de calidad y trazabilidad</footer>
    </div>
    @livewireScripts
</body>
</html>
