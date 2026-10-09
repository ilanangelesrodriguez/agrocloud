<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidacionDeteccion extends Model
{
    protected $table = 'validacion_deteccion';
    protected $primaryKey = 'id_validacion';
    protected $guarded = ['id_validacion'];

    protected function casts(): array { return ['confirmada' => 'boolean']; }
}
