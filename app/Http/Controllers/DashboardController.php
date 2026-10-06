<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user()?->loadMissing(['rol', 'empresa']);
        $globalStats = null;
        $recentCompanies = collect();

        if ($user?->isSuperAdmin()) {
            $globalStats = [
                'empresas' => Empresa::query()->count(),
                'empresas_activas' => Empresa::query()->where('estado', true)->count(),
                'usuarios' => User::query()->whereNotNull('id_empresa')->count(),
                'administradores' => User::query()
                    ->whereHas('rol', fn (Builder $query) => $query->where('codigo', 'ADMIN'))
                    ->count(),
            ];

            $recentCompanies = Empresa::query()
                ->withCount('usuarios')
                ->latest('id_empresa')
                ->limit(5)
                ->get();
        }

        return view('dashboard.index', compact('user', 'globalStats', 'recentCompanies'));
    }
}
