<x-layouts.app title="Inspecciones">
    <div class="space-y-6">
        <div class="flex items-start gap-3">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300"><x-ui.icon name="camera" class="h-5 w-5" /></div>
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Inspecciones</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Seguimiento de las inspecciones creadas desde el módulo Muestreos.</p>
            </div>
        </div>
        @include('inspecciones._mensajes')
        <x-ui.card title="Inspecciones registradas">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                <thead class="text-xs uppercase text-slate-500 dark:text-slate-400"><tr><th class="px-3 py-3">Inspección</th><th class="px-3 py-3">Lote</th><th class="px-3 py-3">Inspector</th><th class="px-3 py-3">Fotografías</th><th class="px-3 py-3">Estado</th><th class="px-3 py-3">Acción</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($inspecciones as $insp)
                        <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"><td class="px-3 py-3 font-semibold text-slate-900 dark:text-slate-100">#{{ $insp->id_inspeccion }}</td><td class="px-3 py-3">{{ $insp->muestreo?->lote?->codigo_lote }}</td><td class="px-3 py-3">{{ $insp->inspector?->nombre_completo ?? 'Por asignar' }}</td><td class="px-3 py-3">{{ $insp->evidencias_count }}</td><td class="px-3 py-3"><span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300"><span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>{{ str_replace('_',' ',$insp->estado) }}</span></td><td class="px-3 py-3"><a href="{{ route('inspecciones.show', $insp->id_inspeccion) }}" class="inline-flex items-center gap-1 font-semibold text-brand-700 dark:text-brand-300">Ver <x-ui.icon name="chevron-right" class="h-4 w-4" /></a></td></tr>
                    @empty<tr><td colspan="6" class="px-3 py-8 text-center text-slate-500">Aún no existen inspecciones. Empieza creando un muestreo.</td></tr>@endforelse
                </tbody>
            </table></div>
            <div class="mt-4">{{ $inspecciones->links() }}</div>
        </x-ui.card>
    </div>
</x-layouts.app>
