<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudAcceso extends Model
{
    protected $table = 'solicitudes_acceso';
    protected $guarded = ['id'];
    protected $hidden = ['invitacion_hash'];
    protected function casts(): array
    {
        return ['historial' => 'array', 'decidida_en' => 'datetime', 'invitacion_expira_en' => 'datetime', 'invitacion_enviada_en' => 'datetime', 'activada_en' => 'datetime'];
    }
    public function empresa() { return $this->belongsTo(Empresa::class, 'id_empresa'); }
    public function usuario() { return $this->belongsTo(User::class, 'id_usuario'); }
    public function revisor() { return $this->belongsTo(User::class, 'revisada_por'); }
}
