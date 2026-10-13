<x-layouts.app title="Reportes operativos">
    <div class="space-y-6"><div><h1 class="text-2xl font-semibold">Reportes operativos</h1><p class="mt-1 text-sm text-slate-500">Consulta el avance de tu empresa por período y descarga los resultados.</p></div>
        <x-ui.card><form method="GET" action="{{ route('reportes.operativos') }}" data-report-filter class="grid items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="text-sm">Reporte<select name="tipo" class="mt-1 w-full rounded-xl border border-slate-300 bg-transparent p-2.5">@foreach($tipos as $key => $label)<option value="{{ $key }}" @selected($tipo === $key)>{{ $label }}</option>@endforeach</select></label>
            <label class="text-sm">Desde<input name="desde" type="date" value="{{ $desde }}" required class="mt-1 w-full rounded-xl border border-slate-300 bg-transparent p-2.5"></label>
            <label class="text-sm">Hasta<input name="hasta" type="date" value="{{ $hasta }}" required class="mt-1 w-full rounded-xl border border-slate-300 bg-transparent p-2.5"></label><x-ui.button type="submit">Consultar</x-ui.button>
        </form></x-ui.card>
        <x-ui.card><div class="mb-4 flex flex-wrap justify-between gap-3"><p class="font-semibold">{{ $tipos[$tipo] }} · {{ number_format($total) }} registros</p><a class="text-sm font-semibold text-brand-700" href="{{ route('reportes.operativos', ['tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta, 'exportar' => 'csv']) }}">Descargar CSV para Excel</a></div>
            <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr>@foreach($columns as $c)<th class="whitespace-nowrap border-b border-slate-200 p-3">{{ $c }}</th>@endforeach</tr></thead><tbody>@forelse($rows as $row)<tr>@foreach($row as $value)<td class="border-b border-slate-100 p-3 dark:border-slate-800">{{ $value ?? '—' }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($columns) }}" class="p-6 text-center text-slate-500">Sin registros para este período.</td></tr>@endforelse</tbody></table></div><div class="mt-4">{{ $registros->links() }}</div>
        </x-ui.card>
    </div>
</x-layouts.app>
