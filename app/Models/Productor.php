<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Productor extends \Illuminate\Database\Eloquent\Model
{
    use BelongsToCompany;

    protected $table = 'productor';

    protected $primaryKey = 'id_productor';

    protected $fillable = [
        'id_empresa',
        'ruc',
        'sunat_verificacion',
        'nombre_razon_social',
        'fundo',
        'ubicacion',
        'departamento',
        'provincia',
        'distrito',
        'ubigeo',
    ];

    protected function casts(): array
    {
        return ['sunat_verificacion' => 'array'];
    }

    /**
     * Empresa a la que pertenece el productor.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(
            Empresa::class,
            'id_empresa',
            'id_empresa'
        );
    }

    /**
     * Lotes registrados para este productor.
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(
            Lote::class,
            'id_productor',
            'id_productor'
        );
    }
}
