<?php

namespace App\Models\Concerns;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::creating(function (Model $model): void {
            $user = auth()->user();

            if ($user && ! $user->isSuperAdmin() && empty($model->id_empresa)) {
                abort_unless($user->id_empresa, 403, 'No existe una empresa activa para esta operación.');
                $model->setAttribute('id_empresa', $user->id_empresa);
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function scopeForCurrentCompany(Builder $query): Builder
    {
        $user = auth()->user();
        abort_unless($user, 401);

        if ($user->isSuperAdmin()) {
            return $query;
        }

        abort_unless($user->id_empresa, 403, 'Tu usuario no tiene una empresa asignada.');

        return $query->where(
            $query->getModel()->qualifyColumn('id_empresa'),
            $user->id_empresa,
        );
    }
}
