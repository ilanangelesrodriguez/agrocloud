<div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Inspecciones</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Captura de evidencias, análisis IA y validación humana de cada lote.</p></div><a wire:navigate href="{{ route('muestreos.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Ver muestreos <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></div>
        @include('inspecciones._mensajes')
        <details class="text-sm"><summary class="cursor-pointer font-semibold text-brand-700 dark:text-brand-300">¿Cómo funciona una inspección?</summary><p class="mt-2 max-w-3xl text-slate-500">Abre un muestreo, captura las fotografías y revisa los resultados IA de cada imagen. Al completar la revisión humana, envía el expediente al supervisor para su dictamen. La IA es una herramienta de apoyo.</p></details>
        <x-ui.card><div class="grid items-end gap-3 sm:grid-cols-3"><label class="text-sm font-medium dark:text-slate-200">Buscar lote<input type="search" maxlength="150" wire:model.live.debounce.400ms="buscar" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"></label><label class="text-sm font-medium dark:text-slate-200">Bandeja<select wire:model.live="cola" class="mt-1 w-full rounded-xl border border-slate-300 bg-white p-2.5 dark:border-slate-600 dark:bg-slate-900"><option value="todas">Todas las inspecciones</option><option value="captura">Captura y revisión del inspector</option><option value="dictamen">Pendientes de dictamen</option><option value="cerradas">Con dictamen registrado</option></select></label>@if(auth()->user()->rol?->codigo === 'INSPECTOR')<label class="flex gap-2 pb-3 text-sm text-slate-500"><input type="checkbox" wire:model.live="misInspecciones">Solo mis inspecciones</label>@endif</div></x-ui.card>
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
                                <td class="whitespace-nowrap px-3 py-3 text-right"><a wire:navigate href="{{ route('inspecciones.show', $insp->id_inspeccion) }}" class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ $insp->estado === 'EN_CAPTURA' && auth()->user()->rol?->codigo === 'INSPECTOR' && (!$insp->id_inspector || (int) $insp->id_inspector === (int) auth()->id()) ? 'Continuar inspección' : 'Ver expediente' }} <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500 dark:text-slate-400">No hay inspecciones en esta bandeja. Revisa los filtros; para iniciar una inspección, abre un muestreo registrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $inspecciones->links() }}</div>
        </x-ui.card>
    </div>
