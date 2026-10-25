<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class FrutoMuestra extends Model
{
    use BelongsToCompany;

    protected $table = 'frutos_muestra';

    protected $guarded = ['id'];

    public function muestreo()
    {
        return $this->belongsTo(Muestreo::class, 'id_muestreo');
    }

    public function evidencias()
    {
        return $this->hasMany(EvidenciaInspeccion::class, 'id_fruto');
    }
}
