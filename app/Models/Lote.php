<?php

namespace App\Models;

use App\Enums\LoteEstado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lote extends Model
{
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

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }
}