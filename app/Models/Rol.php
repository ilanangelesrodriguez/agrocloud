<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'rol';

    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_rol', 'id_rol');
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(
            Permiso::class,
            'rol_permiso',
            'id_rol',
            'id_permiso',
            'id_rol',
            'id_permiso',
        );
    }
}
