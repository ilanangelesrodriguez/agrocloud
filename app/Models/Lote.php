<?php

namespace App\Models;

use App\Enums\LoteEstado;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lote extends Model
{
    use BelongsToCompany;

    public const OPERATIONAL_DATE_SQL = 'COALESCE((SELECT recibido_en FROM recepciones WHERE recepciones.id_lote = lote.id_lote LIMIT 1), lote.fecha_llegada_prevista)';

    public const LOCAL_OPERATIONAL_DATE_SQL = "(".self::OPERATIONAL_DATE_SQL." AT TIME ZONE 'UTC') AT TIME ZONE 'America/Lima'";

    protected $table = 'lote';

    protected $primaryKey = 'id_lote';

    protected $attributes = [
        'estado' => 'PENDIENTE_RECEPCION',
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
        'fecha_llegada_prevista',
        'estado',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $lote) {
            if (!$lote->fecha_llegada_prevista && $lote->fecha_recepcion) $lote->fecha_llegada_prevista = $lote->fecha_recepcion;
        });
    }

    protected function casts(): array
    {
        return [
            'peso_kg' => 'decimal:2',
            'fecha_recepcion' => 'datetime',
            'fecha_llegada_prevista' => 'datetime',
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
    public function getPesoOperativoKgAttribute(): string
    {
        return (string) ($this->recepcion?->peso_recibido_kg ?? $this->peso_kg);
    }

}
