<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Lote;
use App\Models\Productor;
use App\Models\Muestreo;
use App\Models\Inspeccion;
use App\Models\DictamenCalidad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user()?->loadMissing(['rol', 'empresa']);
        $globalStats = null;
        $recentCompanies = collect();
        $metrics = [];
        $quickActions = [];
        $recentLots = collect();
        $recentInspections = collect();
        $pipeline = [];
        $operationalSummary = [];
        $roleDescription = match ($user?->rol?->codigo) {
            'ADMIN' => 'Consulta la operación de tu empresa y administra su equipo.',
            'RECEPCION' => 'Registra productores y recibe los lotes que ingresan a tu empresa.',
            'INSPECTOR' => 'Continúa tus inspecciones y valida las evidencias antes de enviarlas al supervisor.',
            'SUPERVISOR' => 'Revisa los expedientes validados y emite los dictámenes de calidad.',
            'PRODUCCION' => 'Consulta los lotes clasificados y su avance en el control de calidad.',
            default => 'Consulta los módulos disponibles para tu cuenta.',
        };

        if ($user?->isSuperAdmin()) {
            $globalStats = [
                'empresas' => Empresa::query()->count(),
                'empresas_activas' => Empresa::query()->where('estado', true)->count(),
                'usuarios' => User::query()->whereNotNull('id_empresa')->count(),
                'administradores' => User::query()
                    ->whereHas('rol', fn (Builder $query) => $query->where('codigo', 'ADMIN'))
                    ->count(),
                'recepciones_hoy' => \App\Models\Recepcion::whereDate('recibido_en', today())->count(),
                'lotes_activos' => Lote::whereNotIn('estado', ['CLASIFICADO', 'RECHAZADO'])->count(),
                'inspecciones_pendientes' => Inspeccion::where('estado', 'EN_CAPTURA')->count(),
                'dictamenes_pendientes' => Inspeccion::where('estado', 'VALIDADA')->whereDoesntHave('dictamen')->count(),
            ];

            $recentCompanies = Empresa::query()
                ->withCount('usuarios')
                ->latest('id_empresa')
                ->limit(5)
                ->get();
        }

        if (!$user?->isSuperAdmin() && $user?->id_empresa) {
            $lots = Lote::query()->where('id_empresa', $user->id_empresa);
            if ($user->hasPermission('lotes.ver')) {
                $pipeline = (clone $lots)->select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')->get()->map(fn ($row) => ['label' => $row->estado->label(), 'value' => (int) $row->total])->all();
                $operationalSummary = [
                    'Pendientes de recepción' => (clone $lots)->where('estado', 'RECIBIDO')->whereDoesntHave('recepcion')->count(),
                    'Recibidos hoy' => \App\Models\Recepcion::where('id_empresa', $user->id_empresa)->whereDate('recibido_en', today())->count(),
                    'Lotes con más de 48 h sin cierre' => (clone $lots)->whereNotIn('estado', ['CLASIFICADO', 'RECHAZADO'])->where('fecha_recepcion', '<', now()->subHours(48))->count(),
                    'Lotes aprobados últimos 30 días' => (clone $lots)->where('estado', 'CLASIFICADO')->where('updated_at', '>=', now()->subDays(30))->count(),
                ];
            }
            $inspections = Inspeccion::query()->where('id_empresa', $user->id_empresa);
            if ($user->rol?->codigo === 'INSPECTOR') {
                $inspections->where(fn ($query) => $query->where('id_inspector', $user->id_usuario)->orWhereNull('id_inspector'));
            }
            $add = function ($permission, $label, $value, $route) use ($user, &$metrics) {
                if ($user->hasPermission($permission)) {
                    $metrics[] = compact('label', 'route') + ['value' => $value()];
                }
            };
            if ($user->rol?->codigo === 'SUPERVISOR') {
                $add('calidad.ver', 'Dictámenes pendientes', fn () => (clone $inspections)->where('estado', 'VALIDADA')->whereDoesntHave('dictamen')->count(), 'calidad.index');
                foreach (['APROBADO' => 'Aprobados', 'OBSERVADO' => 'Observados', 'RECHAZADO' => 'Rechazados'] as $decision => $label) {
                    $add('calidad.ver', $label, fn () => DictamenCalidad::query()->where('id_empresa', $user->id_empresa)->where('decision', $decision)->count(), $user->hasPermission('lotes.ver') ? 'lotes.index' : null);
                }
                $inspections->where('estado', 'VALIDADA')->whereDoesntHave('dictamen');
            } elseif ($user->rol?->codigo === 'INSPECTOR') {
                $add('inspecciones.ver', 'Inspecciones en captura', fn () => (clone $inspections)->where('estado', 'EN_CAPTURA')->count(), 'inspecciones.index');
                $add('inspecciones.ver', 'Inspecciones validadas', fn () => (clone $inspections)->where('estado', 'VALIDADA')->count(), 'inspecciones.index');
                $add('muestreos.ver', 'Muestreos disponibles', fn () => Muestreo::query()->where('id_empresa', $user->id_empresa)->where('estado', 'REGISTRADO')->count(), 'muestreos.index');
                $inspections->where('estado', 'EN_CAPTURA');
            } else {
                $add('lotes.ver', 'Lotes registrados', fn () => (clone $lots)->count(), 'lotes.index');
                $add('lotes.ver', 'Lotes recibidos', fn () => (clone $lots)->where('estado', 'RECIBIDO')->count(), 'lotes.index');
                $add('lotes.ver', 'Lotes clasificados', fn () => (clone $lots)->where('estado', 'CLASIFICADO')->count(), 'lotes.index');
                $add('productores.ver', 'Productores', fn () => Productor::query()->where('id_empresa', $user->id_empresa)->count(), 'productores.index');
                $add('usuarios.ver', 'Usuarios activos', fn () => User::query()->where('id_empresa', $user->id_empresa)->where('estado', true)->count(), 'usuarios.index');
                $add('calidad.ver', 'Dictámenes pendientes', fn () => (clone $inspections)->where('estado', 'VALIDADA')->whereDoesntHave('dictamen')->count(), 'calidad.index');
            }
            if ($user->rol?->codigo === 'RECEPCION' && $user->hasPermission('recepciones.ver')) {
                $metrics = [
                    ['label' => 'Ingresos por confirmar', 'value' => (clone $lots)->where('estado', 'RECIBIDO')->whereDoesntHave('recepcion')->count(), 'route' => 'recepciones.index'],
                    ['label' => 'Recepciones de hoy', 'value' => \App\Models\Recepcion::where('id_empresa', $user->id_empresa)->whereDate('recibido_en', today())->count(), 'route' => 'recepciones.index'],
                    ['label' => 'Disponibles para muestreo', 'value' => (clone $lots)->where('estado', 'RECIBIDO')->whereHas('recepcion')->count(), 'route' => 'lotes.index'],
                ];
            }
            if ($user->rol?->codigo === 'PRODUCCION' && $user->hasPermission('lotes.ver')) {
                $metrics = [
                    ['label' => 'Lotes disponibles para producción', 'value' => (clone $lots)->where('estado', 'CLASIFICADO')->count(), 'route' => 'lotes.index'],
                    ['label' => 'Kilogramos aprobados', 'value' => (int) round((clone $lots)->where('estado', 'CLASIFICADO')->sum('peso_kg')), 'route' => $user->hasPermission('reportes.operativos') ? 'reportes.operativos' : 'lotes.index'],
                    ['label' => 'Lotes rechazados', 'value' => (clone $lots)->where('estado', 'RECHAZADO')->count(), 'route' => 'lotes.index'],
                ];
                $lots->where('estado', 'CLASIFICADO');
            }
            if ($user->hasPermission('lotes.ver')) $recentLots = (clone $lots)->with('productor')->latest('id_lote')->limit(5)->get();
            if ($user->hasPermission('inspecciones.ver')) $recentInspections = $inspections->with('muestreo.lote')->latest('id_inspeccion')->limit(5)->get();
            foreach ([
                ['empresa.ver', 'Mi empresa', 'empresa.edit'],
                ['productores.crear', 'Registrar productor', 'productores.create'],
                ['recepciones.crear', 'Confirmar ingresos', 'recepciones.index'],
                ['lotes.crear', 'Registrar lote', 'lotes.create'],
                ['reportes.operativos', 'Reportes operativos', 'reportes.operativos'],
                ['muestreos.crear', 'Crear muestreo', 'muestreos.create'],
                ['inspecciones.ver', 'Ver inspecciones', 'inspecciones.index'],
                ['calidad.ver', 'Revisar dictámenes', 'calidad.index'],
                ['usuarios.ver', 'Administrar usuarios', 'usuarios.index'],
            ] as [$permission, $label, $route]) {
                if ($user->hasPermission($permission)) $quickActions[] = compact('label', 'route');
            }
            $quickActions[] = ['label' => 'Mi perfil', 'route' => 'perfil.edit'];
        }

        return view('dashboard.index', compact('user', 'globalStats', 'recentCompanies', 'metrics', 'quickActions', 'recentLots', 'recentInspections', 'roleDescription', 'pipeline', 'operationalSummary'));
    }
}
