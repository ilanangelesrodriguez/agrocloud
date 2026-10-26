<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class SamplingProcedure extends Model
{
    use BelongsToCompany;
    protected $table = 'sampling_procedures';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['requiere_poblacion' => 'boolean', 'activo' => 'boolean', 'max_frutos' => 'integer', 'tamano_recomendado' => 'integer'];
    }
}
