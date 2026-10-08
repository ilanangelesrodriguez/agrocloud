@props(['title' => null, 'description' => null])

<section {{ $attributes->class('rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm') }}>
    @if ($title || $description)
        <header class="border-b border-slate-100 dark:border-slate-700 px-5 py-4 sm:px-6">
            @if ($title)
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</h2>
            @endif
            @if ($description)
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
        </header>
    @endif

    <div class="p-5 sm:p-6">
        {{ $slot }}
    </div>
</section>
