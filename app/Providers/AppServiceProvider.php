<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([\App\Models\Lote::class, \App\Models\Recepcion::class, \App\Models\Muestreo::class, \App\Models\Inspeccion::class, \App\Models\EvidenciaInspeccion::class, \App\Models\ValidacionDeteccion::class, \App\Models\HallazgoManual::class, \App\Models\DictamenCalidad::class] as $model) {
            $model::observe(\App\Observers\OperationalObserver::class);
        }
    }
}
