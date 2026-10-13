<?php

namespace App\Http\Controllers;

use App\Models\DictamenCalidad;
use App\Models\Lote;
use App\Models\Muestreo;
use App\Models\Recepcion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReporteOperativoController extends Controller
{
    public static function types(): array
    {
        $all = ['lotes' => 'Avance de lotes', 'recepciones' => 'Recepciones y pesos', 'muestreos' => 'Muestreos e inspecciones', 'calidad' => 'Dictámenes de calidad'];

        return match (auth()->user()->rol?->codigo) {
            'RECEPCION' => array_intersect_key($all, array_flip(['lotes', 'recepciones'])),
            'INSPECTOR' => array_intersect_key($all, array_flip(['lotes', 'muestreos'])),
            'SUPERVISOR', 'PRODUCCION', 'ADMIN' => $all,
            default => [],
        };
    }

    public function index(Request $request)
    {
        $tipos = self::types();
        abort_unless($tipos, 403);
        $filters = $request->validate(['tipo' => ['nullable', Rule::in(array_keys($tipos))], 'desde' => 'nullable|date_format:Y-m-d', 'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde', 'exportar' => 'nullable|in:csv']);
        $tipo = $filters['tipo'] ?? array_key_first($tipos);
        $desde = $filters['desde'] ?? now()->subDays(29)->toDateString();
        $hasta = $filters['hasta'] ?? now()->toDateString();
        $query = match ($tipo) {
            'recepciones' => Recepcion::query()->with(['lote.productor', 'responsable']),
            'muestreos' => Muestreo::query()->with('lote')->withCount(['frutos', 'inspecciones']),
            'calidad' => DictamenCalidad::query()->with(['lote', 'supervisor']),
            default => Lote::query()->with('productor')->withCount('muestreos'),
        };
        $date = match ($tipo) {
            'recepciones' => 'recibido_en', 'lotes' => 'fecha_recepcion', default => 'created_at'
        };
        $query->where('id_empresa', $request->user()->id_empresa)->where($date, '>=', $desde.' 00:00:00')->where($date, '<', Carbon::parse($hasta)->addDay()->toDateString().' 00:00:00');
        if ($tipo === 'muestreos' && $request->user()->rol?->codigo === 'INSPECTOR') {
            $query->where('id_inspector', $request->user()->id_usuario);
        }
        $query->orderByDesc($date);
        $columns = match ($tipo) {
            'recepciones' => ['Lote', 'Productor', 'Documento', 'Peso recibido (kg)', 'Responsable', 'Fecha'],
            'muestreos' => ['Lote', 'Muestreo', 'Método', 'Tamaño', 'Frutos identificados', 'Inspecciones', 'Estado', 'Fecha'],
            'calidad' => ['Lote', 'Inspección', 'Decisión', 'Categoría', 'Motivo', 'Supervisor', 'Fecha'],
            default => ['Lote', 'Productor', 'Producto', 'Variedad', 'Peso (kg)', 'Estado', 'Muestreos', 'Fecha'],
        };
        $map = fn ($r) => match ($tipo) {
            'recepciones' => [$r->lote?->codigo_lote, $r->lote?->productor?->nombre_razon_social, $r->documento, $r->peso_recibido_kg, $r->responsable?->nombre_completo, $r->recibido_en?->format('d/m/Y H:i')],
            'muestreos' => [$r->lote?->codigo_lote, $r->id_muestreo, $r->metodo, $r->tamano_muestra, $r->frutos_count, $r->inspecciones_count, $r->estado, $r->created_at?->format('d/m/Y H:i')],
            'calidad' => [$r->lote?->codigo_lote, $r->id_inspeccion, $r->decision, $r->categoria, $r->motivo, $r->supervisor?->nombre_completo, $r->created_at?->format('d/m/Y H:i')],
            default => [$r->codigo_lote, $r->productor?->nombre_razon_social, $r->producto, $r->variedad, $r->peso_kg, $r->estado->label(), $r->muestreos_count, $r->fecha_recepcion?->format('d/m/Y H:i')],
        };
        if ($request->input('exportar') === 'csv') {
            return response()->streamDownload(function () use ($query, $map, $columns) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, $columns, ';', '"', '');
                foreach ($query->lazy(500) as $row) {
                    $values = array_map(fn ($value) => preg_match('/^[\s]*[=+@-]/u', (string) $value) ? "'".$value : (string) $value, $map($row));
                    fputcsv($out, $values, ';', '"', '');
                }
                fclose($out);
            }, 'reporte-'.$tipo.'-'.$desde.'-'.$hasta.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
        }
        $total = (clone $query)->count();
        $registros = $query->paginate(30)->withQueryString();
        $rows = $registros->map($map);

        return view('operaciones.reportes', compact('tipos', 'tipo', 'desde', 'hasta', 'total', 'registros', 'columns', 'rows'));
    }
}
