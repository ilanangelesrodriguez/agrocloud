<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Recepcion extends Model
{
    use BelongsToCompany;

    protected $table = 'recepciones';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['recibido_en' => 'datetime'];
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
