<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $primaryKey = 'id_empresa';

    protected $fillable = [
        'ruc',
        'sunat_verificacion',
        'razon_social',
        'nombre_comercial',
        'grupo_empresarial',
        'tipo_empresa',
        'rubro',
        'descripcion',
        'direccion',
        'ubigeo',
        'departamento',
        'provincia',
        'distrito',
        'telefono',
        'correo',
        'sitio_web',
        'logo_url',
        'logo_public_id',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'sunat_verificacion' => 'array',
            'estado' => 'boolean',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_empresa', 'id_empresa');
    }
}
