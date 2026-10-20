<x-layouts.app title="Recepción de lotes">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Recepción de lotes</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Registra el ingreso físico antes de entregar el lote al inspector.</p>
            </div>
            @if(auth()->user()->hasPermission('lotes.crear'))
                <a wire:navigate href="{{ route('lotes.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-200 dark:focus:ring-brand-900/50">
                    <x-ui.icon name="plus" class="h-4 w-4" />
                    Registrar lote de ingreso
                </a>
            @endif
        </div>

        <x-ui.card title="Por confirmar" description="Revisa los datos físicos antes de habilitar el lote para muestreo.">
            <div class="space-y-3">
                @forelse($pendientes as $lote)
                    <details class="group overflow-hidden rounded-xl border border-slate-200 bg-white transition open:border-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:open:border-brand-800">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-4 [&::-webkit-details-marker]:hidden">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900 dark:text-slate-100">{{ $lote->codigo_lote }}</p>
                                    <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $lote->producto }} · {{ $lote->productor?->nombre_razon_social ?? 'Sin productor' }}</p>
                                </div>
                            </div>
                            <div class="hidden shrink-0 text-right sm:block">
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ number_format((float) $lote->peso_kg, 2) }} kg</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Peso declarado</p>
                            </div>
                            <x-ui.icon name="chevron-down" class="h-5 w-5 shrink-0 text-slate-400 transition group-open:rotate-180" />
                        </summary>
                        <div class="border-t border-slate-200 bg-white px-4 pb-5 pt-4 dark:border-slate-700 dark:bg-slate-900">
                            @if(auth()->user()->hasPermission('recepciones.crear') && auth()->user()->rol?->codigo === 'RECEPCION')
                                <form data-ajax-form method="POST" action="{{ route('recepciones.store', $lote->id_lote) }}" class="grid gap-4 sm:grid-cols-2">
                                    @csrf
                                    @foreach(['documento' => 'Guía / documento de ingreso', 'transportista' => 'Transportista (opcional)', 'placa' => 'Placa (opcional)'] as $name => $label)
                                        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label }}
                                            <input name="{{ $name }}" {{ $name === 'documento' ? 'required' : '' }} maxlength="{{ $name === 'placa' ? 20 : ($name === 'documento' ? 100 : 150) }}" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50">
                                        </label>
                                    @endforeach
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Peso recibido (kg)
                                        <input name="peso_recibido_kg" type="number" step="0.01" min="0.01" max="99999999.99" value="{{ $lote->peso_kg }}" required class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50">
                                    </label>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Fecha y hora de recepción
                                        <input name="recibido_en" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50">
                                    </label>
                                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200 sm:col-span-2">Condiciones de ingreso y diferencias de peso
                                        <textarea name="observaciones" placeholder="Describe las condiciones del ingreso. Si el peso recibido difiere del declarado, explica la diferencia." maxlength="3000" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-900/50"></textarea>
                                    </label>
                                    <div class="flex justify-end sm:col-span-2">
                                        <x-ui.button type="submit"><x-ui.icon name="check" class="mr-2 h-4 w-4" />Confirmar recepción</x-ui.button>
                                    </div>
                                </form>
                            @else
                                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                    <x-ui.icon name="alert-triangle" class="h-5 w-5 shrink-0" />
                                    La confirmación corresponde al personal de recepción.
                                </div>
                            @endif
                        </div>
                    </details>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">No hay ingresos pendientes de confirmar.</p>
                    </div>
                @endforelse
            </div>
            @if($pendientes->hasPages())
                <div class="mt-5 border-t border-slate-100 pt-5 dark:border-slate-700">{{ $pendientes->links() }}</div>
            @endif
        </x-ui.card>

        <x-ui.card title="Últimas recepciones confirmadas" description="Consulta los últimos ingresos registrados en la empresa.">
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recibidas as $r)
                    <div class="flex flex-wrap items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-ui.icon name="check" class="h-5 w-5" /></div>
                            <div class="min-w-0">
                                <a wire:navigate class="font-semibold text-brand-700 hover:underline dark:text-brand-300" href="{{ route('trazabilidad.show', $r->id_lote) }}">{{ $r->lote?->codigo_lote }}</a>
                                <p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ $r->documento }} · {{ $r->responsable?->nombre_completo }}</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $r->peso_recibido_kg }} kg · {{ $r->recibido_en->format('d/m/Y H:i') }}</span>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Aún no hay recepciones confirmadas.</p>
                    </div>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
