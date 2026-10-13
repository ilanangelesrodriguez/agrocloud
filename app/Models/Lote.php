<?php

namespace App\Models;

use App\Enums\LoteEstado;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lote extends Model
{
    use BelongsToCompany;

    protected $table = 'lote';

    protected $primaryKey = 'id_lote';

    protected $attributes = [
        'estado' => 'RECIBIDO',
    ];

    protected $fillable = [
        'id_empresa',
        'id_productor',
        'codigo_lote',
        'producto',
        'variedad',
        'peso_kg',
        'unidad_medida',
        'fecha_recepcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'peso_kg' => 'decimal:2',
            'fecha_recepcion' => 'datetime',
            'estado' => LoteEstado::class,
        ];
    }

    public function productor(): BelongsTo
    {
        return $this->belongsTo(
            Productor::class,
            'id_productor',
            'id_productor'
        );
    }

    public function recepcion() { return $this->hasOne(Recepcion::class, 'id_lote'); }
    public function muestreos() { return $this->hasMany(Muestreo::class, 'id_lote'); }
}
