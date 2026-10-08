<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inspeccion extends Model
{
    use BelongsToCompany;

    protected $table = 'inspeccion';
    protected $primaryKey = 'id_inspeccion';
    protected $guarded = ['id_inspeccion'];

    protected function casts(): array { return ['validada_en' => 'datetime']; }

    public function muestreo(): BelongsTo { return $this->belongsTo(Muestreo::class, 'id_muestreo', 'id_muestreo'); }
    public function inspector(): BelongsTo { return $this->belongsTo(User::class, 'id_inspector', 'id_usuario'); }
    public function evidencias(): HasMany { return $this->hasMany(EvidenciaInspeccion::class, 'id_inspeccion', 'id_inspeccion'); }
    public function hallazgos(): HasMany { return $this->hasMany(HallazgoManual::class, 'id_inspeccion', 'id_inspeccion'); }
    public function dictamen(): HasOne { return $this->hasOne(DictamenCalidad::class, 'id_inspeccion', 'id_inspeccion'); }
}
