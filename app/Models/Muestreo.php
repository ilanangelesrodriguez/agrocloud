<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Muestreo extends Model
{
    use BelongsToCompany;

    protected $table = 'muestreo';
    protected $primaryKey = 'id_muestreo';
    protected $guarded = ['id_muestreo'];

    public function lote(): BelongsTo { return $this->belongsTo(Lote::class, 'id_lote', 'id_lote'); }
    public function frutos() { return $this->hasMany(FrutoMuestra::class, 'id_muestreo'); }
    public function inspector(): BelongsTo { return $this->belongsTo(User::class, 'id_inspector', 'id_usuario'); }
    public function inspecciones(): HasMany { return $this->hasMany(Inspeccion::class, 'id_muestreo', 'id_muestreo'); }
}
