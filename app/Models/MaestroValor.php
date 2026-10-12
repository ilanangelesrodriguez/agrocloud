<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaestroValor extends Model
{
    protected $table = 'maestro_valores';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['estado' => 'boolean', 'id_empresa' => 'integer', 'parent_id' => 'integer'];
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function scopeVisibleTo($query, string $type, ?int $company)
    {
        $scope = config('maestros.'.$type.'.scope');
        $query->where('tipo', $type);
        if ($scope === 'global') return $query->whereNull('id_empresa');
        if ($scope === 'company') return $company ? $query->where('id_empresa', $company) : $query->whereRaw('1 = 0');

        return $query->where(fn ($q) => $q->whereNull('id_empresa')->when($company, fn ($q) => $q->orWhere('id_empresa', $company)));
    }
}
