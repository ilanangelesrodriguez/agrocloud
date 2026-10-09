<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallazgoManual extends Model
{
    protected $table = 'hallazgo_manual';
    protected $primaryKey = 'id_hallazgo';
    protected $guarded = ['id_hallazgo'];
}
