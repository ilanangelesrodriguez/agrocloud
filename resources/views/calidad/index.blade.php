<x-layouts.app title="Pendientes de calidad">
    <div class="space-y-6">
        <div><h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Dictámenes pendientes</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inspecciones validadas por control de calidad que requieren una decisión del supervisor.</p></div>
        @include('inspecciones._mensajes')
        <x-ui.card title="Inspecciones listas para decisión">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                <thead class="text-xs uppercase text-slate-500 dark:text-slate-400"><tr><th class="px-3 py-3">Inspección</th><th class="px-3 py-3">Lote</th><th class="px-3 py-3">Fecha</th><th class="px-3 py-3">Acción</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($inspecciones as $i)
                        <tr><td class="px-3 py-3">#{{ $i->id_inspeccion }}</td><td class="px-3 py-3">{{ $i->muestreo?->lote?->codigo_lote }}</td><td class="px-3 py-3">{{ $i->validada_en?->format('d/m/Y H:i') ?? '—' }}</td><td class="px-3 py-3"><a class="font-semibold text-brand-700 dark:text-brand-300" href="{{ route('calidad.show', $i->id_inspeccion) }}">Evaluar</a></td></tr>
                    @empty<tr><td colspan="4" class="px-3 py-8 text-center text-slate-500">No hay inspecciones pendientes de dictamen.</td></tr>@endforelse
                </tbody>
            </table></div>
            <div class="mt-4">{{ $inspecciones->links() }}</div>
        </x-ui.card>
    </div>
</x-layouts.app>
