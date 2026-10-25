@if(auth()->check())
<div class="relative shrink-0" data-notifications-root data-notifications-feed="{{ route('notificaciones.feed') }}" data-notifications-user="{{ auth()->id() }}" data-notifications-company="{{ auth()->user()->id_empresa ?? '' }}">
    @php($unread = auth()->user()->unreadNotifications()->count())
    <button type="button" data-notifications-toggle aria-controls="notifications-panel" aria-label="Abrir notificaciones" aria-expanded="false"
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-600 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z"/><path d="M10 21h4"/></svg>
        <span data-notifications-badge class="{{ $unread ? '' : 'hidden ' }}absolute -right-1 -top-1 min-w-5 rounded-full bg-red-600 px-1 text-center text-[10px] font-bold leading-5 text-white">{{ $unread > 99 ? '99+' : $unread }}</span>
    </button>
    <section id="notifications-panel" data-notifications-panel aria-label="Notificaciones recientes" class="fixed inset-x-3 top-[72px] z-50 hidden sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-[390px] sm:max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-700">
            <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">Notificaciones</span>
            <div class="flex items-center gap-3"><a href="{{ route('notificaciones.index') }}" wire:navigate class="text-xs font-semibold text-brand-700 hover:underline dark:text-brand-300">Ver todas</a><button type="button" data-notifications-close aria-label="Cerrar notificaciones" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"><x-ui.icon name="x" class="h-4 w-4" /></button></div>
        </div>
        <div data-notifications-list class="max-h-[min(370px,calc(100dvh-160px))] overflow-y-auto p-2">
            <p class="px-3 py-6 text-center text-sm text-slate-500">Cargando notificaciones…</p>
        </div>
    </section>
</div>
@endif
