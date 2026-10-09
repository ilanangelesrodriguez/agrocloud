<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DictamenCalidad extends Model
{
    protected $table = 'dictamen_calidad';
    protected $primaryKey = 'id_dictamen';
    protected $guarded = ['id_dictamen'];

    public function inspeccion(): BelongsTo { return $this->belongsTo(Inspeccion::class, 'id_inspeccion', 'id_inspeccion'); }
}
