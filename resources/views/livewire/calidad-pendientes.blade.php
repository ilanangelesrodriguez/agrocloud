<div class="space-y-5">
        <div><h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Dictámenes pendientes</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Las inspecciones llegan aquí después de que el inspector confirma o descarta todas sus detecciones y finaliza la revisión.</p></div>
        @include('inspecciones._mensajes')
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-200"><strong>Supervisor:</strong> revisa las evidencias, predicciones verificadas y observaciones; luego aprueba, observa o rechaza. Un lote observado regresa a reinspección.</div>
        <x-ui.card title="Inspecciones listas para dictamen">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700"><thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-800/50 dark:text-slate-400"><tr><th class="px-3 py-3">Inspección</th><th class="px-3 py-3">Lote</th><th class="px-3 py-3">Validada por inspector</th><th class="px-3 py-3 text-right">Acción</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($inspecciones as $inspeccion)
                    <tr wire:key="calidad-{{ $inspeccion->id_inspeccion }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/40"><td class="px-3 py-3 font-semibold text-slate-900 dark:text-slate-100">#{{ $inspeccion->id_inspeccion }}</td><td class="px-3 py-3 text-slate-700 dark:text-slate-200">{{ $inspeccion->muestreo?->lote?->codigo_lote }}</td><td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ $inspeccion->validada_en?->format('d/m/Y H:i') ?? '—' }}</td><td class="px-3 py-3 text-right"><a wire:navigate href="{{ route('calidad.show', $inspeccion->id_inspeccion) }}" class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ auth()->user()->hasPermission('calidad.clasificar') ? 'Evaluar' : 'Consultar' }} <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No hay inspecciones pendientes de dictamen.</td></tr>
                @endforelse
            </tbody></table></div>
            <div class="mt-4">{{ $inspecciones->links() }}</div>
        </x-ui.card>
    </div>
