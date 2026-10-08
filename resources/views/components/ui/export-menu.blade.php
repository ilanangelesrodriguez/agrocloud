@props(['items' => []])

<details class="group relative">
    <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-100 dark:focus:ring-slate-700 [&::-webkit-details-marker]:hidden">
        <x-ui.icon name="download" class="h-4 w-4" />
        Exportar
        <x-ui.icon name="chevron-down" class="h-4 w-4 text-slate-400 transition group-open:rotate-180" />
    </summary>

    <div class="absolute right-0 z-20 mt-2 w-60 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2 shadow-xl">
        @foreach ($items as $item)
            <a
                href="{{ $item['href'] }}"
                @if (($item['target'] ?? null) === '_blank') target="_blank" rel="noopener noreferrer" @endif
                class="flex items-start gap-3 rounded-xl px-3 py-2.5 transition hover:bg-slate-50 dark:hover:bg-slate-700"
            >
                <div class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-50 dark:bg-brand-900/35 text-brand-700 dark:text-brand-300">
                    <x-ui.icon :name="$item['icon'] ?? 'download'" class="h-4 w-4" />
                </div>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $item['label'] }}</span>
                    @if (! empty($item['description']))
                        <span class="mt-0.5 block text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $item['description'] }}</span>
                    @endif
                </span>
            </a>
        @endforeach
    </div>
</details>
