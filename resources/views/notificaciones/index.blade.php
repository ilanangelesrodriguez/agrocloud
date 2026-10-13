<x-layouts.app title="Notificaciones">
    <div class="mx-auto max-w-4xl space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Notificaciones</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Avisos operativos dirigidos a tu usuario. Se conservan incluso cuando estás desconectado.</p>
            </div>
            @if(auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notificaciones.leer-todas') }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary">Marcar todas como leídas</x-ui.button>
                </form>
            @endif
        </div>
        <x-ui.card>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($notificaciones as $n)
                    @php($d = $n->data)
                    <div class="flex flex-wrap items-start justify-between gap-3 py-4 first:pt-0 last:pb-0 {{ $n->read_at ? 'opacity-75' : '' }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                @if(!$n->read_at)<span class="h-2 w-2 rounded-full bg-brand-500" title="Sin leer"></span>@endif
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $d['titulo'] ?? 'Notificación' }}</p>
                                <span class="text-xs text-slate-500">{{ $n->created_at?->locale('es')->diffForHumans() }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $d['mensaje'] ?? '' }}</p>
                            <p class="mt-2 text-xs text-slate-500">{{ str_replace('_', ' ', $d['tipo'] ?? 'GENERAL') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('notificaciones.abrir', $n->id) }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-brand-300">Abrir</a>
                            @if(!$n->read_at)
                                <form method="POST" action="{{ route('notificaciones.leer', $n->id) }}">@csrf
                                    <button type="submit" class="text-xs text-slate-500 hover:text-brand-700">Marcar leída</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-sm text-slate-500">No tienes notificaciones todavía.</div>
                @endforelse
            </div>
            <div class="mt-5">{{ $notificaciones->links() }}</div>
        </x-ui.card>
    </div>
</x-layouts.app>
