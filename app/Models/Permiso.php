<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permiso extends Model
{
    protected $table = 'permiso';

    protected $primaryKey = 'id_permiso';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'modulo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'rol_permiso',
            'id_permiso',
            'id_rol',
            'id_permiso',
            'id_rol',
        );
    }
}
