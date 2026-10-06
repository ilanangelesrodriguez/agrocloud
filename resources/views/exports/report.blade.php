<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} · AgroCloud AI</title>
    <style>
        *{box-sizing:border-box} body{margin:0;background:#f8fafc;color:#0f172a;font-family:Arial,Helvetica,sans-serif}.page{max-width:1320px;margin:30px auto;padding:0 24px}.toolbar{display:flex;justify-content:flex-end;margin-bottom:14px}.btn{border:0;border-radius:10px;background:#158a55;color:#fff;padding:10px 15px;font-weight:700;cursor:pointer}.report{background:#fff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(15,23,42,.06)}.header{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:28px 30px;border-bottom:1px solid #e2e8f0}.brand{display:flex;align-items:center;gap:14px}.mark{display:grid;place-items:center;width:48px;height:48px;border-radius:14px;background:#158a55;color:white;font-weight:800}.logo{max-width:100px;max-height:54px;object-fit:contain}.eyebrow{font-size:11px;text-transform:uppercase;letter-spacing:.18em;color:#158a55;font-weight:800}.title{font-size:25px;margin:6px 0 0}.subtitle{font-size:13px;color:#64748b;margin-top:6px}.meta{display:flex;flex-wrap:wrap;gap:8px;padding:14px 30px;background:#f8fafc;border-bottom:1px solid #e2e8f0}.chip{font-size:11px;color:#475569;background:white;border:1px solid #e2e8f0;border-radius:999px;padding:6px 10px}.table-wrap{overflow-x:auto;padding:20px 24px 26px}table{width:100%;border-collapse:collapse;font-size:11px}th{background:#158a55;color:#fff;text-transform:uppercase;letter-spacing:.04em;text-align:left;padding:10px 9px;border:1px solid #147748}td{padding:9px;border:1px solid #e2e8f0;vertical-align:top}tbody tr:nth-child(even){background:#f8fafc}.status{display:inline-block;border-radius:999px;padding:4px 8px;font-weight:700}.status.on{background:#ecfdf5;color:#047857}.status.off{background:#f1f5f9;color:#475569}.footer{display:flex;justify-content:space-between;gap:20px;padding:15px 30px;border-top:1px solid #e2e8f0;color:#94a3b8;font-size:10px}
        @media print{body{background:#fff}.page{max-width:none;margin:0;padding:0}.toolbar{display:none}.report{border:0;border-radius:0;box-shadow:none}.header{padding:18px 20px}.meta{padding:10px 20px}.table-wrap{padding:14px 16px}.footer{padding:10px 20px}thead{display:table-header-group}tr{page-break-inside:avoid}}
        @page{size:A4 landscape;margin:10mm}
    </style>
</head>
<body>
<div class="page">
    <div class="toolbar"><button class="btn" onclick="window.print()">Imprimir / Guardar PDF</button></div>
    <article class="report">
        <header class="header">
            <div class="brand">
                @if(!empty($logoUrl ?? null))<img class="logo" src="{{ $logoUrl }}" alt="Logo">@else<div class="mark">AC</div>@endif
                <div><div class="eyebrow">AgroCloud AI</div><h1 class="title">{{ $title }}</h1><div class="subtitle">{{ $subtitle ?? 'Reporte exportable' }}</div></div>
            </div>
            <div style="text-align:right;color:#64748b;font-size:11px"><strong style="color:#334155">Generado</strong><br>{{ now()->format('d/m/Y H:i') }}</div>
        </header>
        @if(!empty($meta))<div class="meta">@foreach($meta as $label=>$value)<span class="chip"><strong>{{ $label }}:</strong> {{ $value }}</span>@endforeach</div>@endif
        <div class="table-wrap"><table><thead><tr>@foreach($columns as $column)<th>{{ $column['label'] }}</th>@endforeach</tr></thead><tbody>@forelse($rows as $row)<tr>@foreach($columns as $column)@php($value=$row[$column['key']] ?? '—')<td>@if(($column['status'] ?? false) === true)@php($on=in_array(strtolower(trim((string)$value)),['activo','activa','sí','si'],true))<span class="status {{ $on ? 'on' : 'off' }}">{{ $value }}</span>@else{{ $value }}@endif</td>@endforeach</tr>@empty<tr><td colspan="{{ count($columns) }}" style="text-align:center;padding:30px;color:#64748b">No hay registros para los filtros seleccionados.</td></tr>@endforelse</tbody></table></div>
        <footer class="footer"><span>AgroCloud AI · Gestión agrícola inteligente</span><span>{{ count($rows) }} registro(s)</span></footer>
    </article>
</div>
</body>
</html>
