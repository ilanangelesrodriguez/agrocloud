<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvidenciaInspeccion extends Model
{
    use BelongsToCompany;

    protected $table = 'evidencia_inspeccion';
    protected $primaryKey = 'id_evidencia';
    protected $attributes = ['pertinencia' => 'PENDIENTE'];
    protected $guarded = ['id_evidencia'];

    protected function casts(): array { return ['resultado_ia' => 'array']; }

    public function inspeccion(): BelongsTo { return $this->belongsTo(Inspeccion::class, 'id_inspeccion', 'id_inspeccion'); }
    public function fruto() { return $this->belongsTo(FrutoMuestra::class, 'id_fruto'); }
    public function validaciones(): HasMany { return $this->hasMany(ValidacionDeteccion::class, 'id_evidencia', 'id_evidencia'); }
}
