<div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Inspecciones</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Captura de evidencias, análisis IA y validación humana de cada lote.</p></div><a wire:navigate href="{{ route('muestreos.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Ver muestreos <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></div>
        @include('inspecciones._mensajes')
        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-200"><span class="font-semibold">Flujo:</span> <span class="inline-flex flex-wrap items-center gap-1 align-middle"><strong>Crear muestreo</strong><x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /><strong>Capturar fotos</strong><x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /><strong>Analizar con IA</strong><x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /><strong>Validar detecciones</strong><x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /><strong>Supervisor emite dictamen</strong></span>. Los resultados de IA son orientativos.</div>
        <x-ui.card title="Inspecciones registradas" description="Continúa una inspección abierta o consulta un expediente ya validado.">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-800/50 dark:text-slate-400"><tr><th class="px-3 py-3 font-semibold">Inspección</th><th class="px-3 py-3 font-semibold">Lote / Inspector</th><th class="px-3 py-3 font-semibold">Fotografías</th><th class="px-3 py-3 font-semibold">Estado</th><th class="px-3 py-3 text-right font-semibold">Acción</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($inspecciones as $insp)
                            <tr wire:key="inspeccion-{{ $insp->id_inspeccion }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="whitespace-nowrap px-3 py-3 font-semibold text-slate-900 dark:text-slate-100">#{{ $insp->id_inspeccion }}</td>
                                <td class="px-3 py-3"><p class="font-medium text-slate-800 dark:text-slate-200">{{ $insp->muestreo?->lote?->codigo_lote }}</p><p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $insp->inspector?->nombre_completo ?? 'Pendiente de asignación' }}</p></td>
                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">{{ $insp->evidencias_count }} registradas</td>
                                <td class="px-3 py-3"><x-ui.state-badge :state="$insp->estado" /></td>
                                <td class="whitespace-nowrap px-3 py-3 text-right"><a wire:navigate href="{{ route('inspecciones.show', $insp->id_inspeccion) }}" class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ $insp->estado === 'EN_CAPTURA' ? 'Continuar' : 'Ver expediente' }} <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">Todavía no hay inspecciones. Crea un muestreo para empezar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $inspecciones->links() }}</div>
        </x-ui.card>
    </div>
