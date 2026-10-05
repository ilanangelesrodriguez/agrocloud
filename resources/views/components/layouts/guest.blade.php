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
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="hidden bg-brand-700 p-10 text-white lg:flex lg:flex-col lg:justify-between xl:p-14">
            <div>
                <div class="inline-flex items-center gap-3">
                    <div class="grid h-11 w-11 place-items-center rounded-2xl bg-white/15 font-bold">AC</div>
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

        <section class="flex items-center justify-center p-5 sm:p-8 lg:p-12">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </section>
    </main>
</body>
</html>
