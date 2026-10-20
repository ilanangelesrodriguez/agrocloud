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
        $all = ['lotes' => 'Avance de lotes', 'recepciones' => 'Recepciones y pesos', 'muestreos' => 'Muestreos e inspecciones', 'calidad' => 'Dictámenes de calidad', 'ia' => 'Revisión y rendimiento del servicio IA'];

        return match (auth()->user()->rol?->codigo) {
            'RECEPCION' => array_intersect_key($all, array_flip(['lotes', 'recepciones'])),
            'INSPECTOR' => array_intersect_key($all, array_flip(['lotes', 'muestreos', 'ia'])),
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
            'ia' => \App\Models\EvidenciaInspeccion::query()->with(['inspeccion.muestreo.lote', 'validaciones']),
            'recepciones' => Recepcion::query()->with(['lote.productor', 'responsable']),
            'muestreos' => Muestreo::query()->with('lote')->withCount(['frutos', 'inspecciones']),
            'calidad' => DictamenCalidad::query()->with(['lote', 'supervisor', 'inspeccion.evidencias.validaciones', 'inspeccion.hallazgos', 'inspeccion.muestreo'])->with(['inspeccion.muestreo' => fn ($q) => $q->withCount('frutos')]),
            default => Lote::query()->with(['productor', 'recepcion'])->withCount('muestreos'),
        };
        $date = match ($tipo) {
            'recepciones' => 'recibido_en', 'lotes' => 'fecha_recepcion', default => 'created_at'
        };
        $query->where('id_empresa', $request->user()->id_empresa)->where($date, '>=', $desde.' 00:00:00')->where($date, '<', Carbon::parse($hasta)->addDay()->toDateString().' 00:00:00');
        if ($tipo === 'muestreos' && $request->user()->rol?->codigo === 'INSPECTOR') {
            $query->where('id_inspector', $request->user()->id_usuario);
        }
        if ($tipo === 'ia' && $request->user()->rol?->codigo === 'INSPECTOR') $query->whereHas('inspeccion', fn ($q) => $q->where('id_inspector', $request->user()->id_usuario));
        $query->orderByDesc($date);
        $columns = match ($tipo) {
            'ia' => ['Lote', 'Inspección', 'Fotografía', 'Fruto', 'Estado IA', 'Versión', 'Detecciones', 'Confirmadas por inspector', 'Descartadas por inspector', 'Fecha'],
            'recepciones' => ['Lote', 'Productor', 'Documento', 'Peso declarado (kg)', 'Peso recibido (kg)', 'Diferencia (kg)', 'Responsable', 'Fecha'],
            'muestreos' => ['Lote', 'Muestreo', 'Método', 'Tamaño', 'Frutos identificados', 'Inspecciones', 'Estado', 'Fecha'],
            'calidad' => ['Lote', 'Inspección', 'Decisión', 'Categoría', 'Motivo', 'Supervisor', 'Frutos de muestra', 'Imágenes', 'Frutos con hallazgos', 'Frutos observados (%)', 'Fecha'],
            default => ['Lote', 'Productor', 'Producto', 'Variedad', 'Peso declarado (kg)', 'Peso recibido (kg)', 'Estado', 'Muestreos', 'Fecha'],
        };
        $map = fn ($r) => match ($tipo) {
            'ia' => [$r->inspeccion?->muestreo?->lote?->codigo_lote, $r->id_inspeccion, $r->id_evidencia, $r->id_fruto, $r->estado_ia, $r->version_modelo, count($r->resultado_ia['detections'] ?? []), $r->validaciones->where('confirmada', true)->count(), $r->validaciones->where('confirmada', false)->count(), $r->created_at?->format('d/m/Y H:i')],
            'recepciones' => [$r->lote?->codigo_lote, $r->lote?->productor?->nombre_razon_social, $r->documento, $r->lote?->peso_kg, $r->peso_recibido_kg, round((float) $r->peso_recibido_kg - (float) $r->lote?->peso_kg, 2), $r->responsable?->nombre_completo, $r->recibido_en?->format('d/m/Y H:i')],
            'muestreos' => [$r->lote?->codigo_lote, $r->id_muestreo, $r->metodo, $r->tamano_muestra, $r->frutos_count, $r->inspecciones_count, $r->estado, $r->created_at?->format('d/m/Y H:i')],
            'calidad' => [$r->lote?->codigo_lote, $r->id_inspeccion, $r->decision, $r->categoria, $r->motivo, $r->supervisor?->nombre_completo, $r->inspeccion?->muestreo?->tamano_muestra, $r->inspeccion?->evidencias->count(), $this->frutosObservados($r), ($r->inspeccion?->muestreo?->frutos_count ?? 0) > 0 && $r->inspeccion?->muestreo?->tamano_muestra ? round($this->frutosObservados($r) * 100 / $r->inspeccion->muestreo->tamano_muestra, 2) : null, $r->created_at?->format('d/m/Y H:i')],
            default => [$r->codigo_lote, $r->productor?->nombre_razon_social, $r->producto, $r->variedad, $r->peso_kg, $r->recepcion?->peso_recibido_kg, $r->estado->label(), $r->muestreos_count, $r->fecha_recepcion?->format('d/m/Y H:i')],
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
        $evidenciasIds = $tipo === 'ia' ? (clone $query)->reorder()->select('id_evidencia') : null;
        $total = (clone $query)->count();
        $registros = $query->paginate(30)->withQueryString();
        $rows = $registros->map($map);

        $rendimiento = null;
        if ($tipo === 'ia') {
            $intentos = \Illuminate\Support\Facades\DB::table('eventos_trazabilidad')->where('id_empresa', $request->user()->id_empresa)->whereIn('evento', ['ANALISIS_COMPLETADO', 'ANALISIS_FALLIDO'])->whereBetween('created_at', [$desde.' 00:00:00', $hasta.' 23:59:59'])->whereIn('registro', $evidenciasIds)->get();
            $rendimiento = ['completados' => $intentos->where('evento', 'ANALISIS_COMPLETADO')->count(), 'fallidos' => $intentos->where('evento', 'ANALISIS_FALLIDO')->count(), 'promedio_ms' => (int) round($intentos->map(fn ($e) => json_decode($e->cambios, true)['duracion_ms'] ?? 0)->avg() ?? 0)];
        }
        return view('operaciones.reportes', compact('rendimiento', 'tipos', 'tipo', 'desde', 'hasta', 'total', 'registros', 'columns', 'rows'));
    }
    private function frutosObservados(DictamenCalidad $dictamen): int
    {
        $inspection = $dictamen->inspeccion;
        if (!$inspection) return 0;
        $manuales = $inspection->hallazgos->pluck('id_evidencia')->filter();
        return $inspection->evidencias->filter(fn ($foto) => $foto->validaciones->contains('confirmada', true) || $manuales->contains($foto->id_evidencia))->pluck('id_fruto')->filter()->unique()->count();
    }

}
