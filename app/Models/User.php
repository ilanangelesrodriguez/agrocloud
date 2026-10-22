<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_empresa',
        'id_rol',
        'nombre',
        'apellido',
        'correo',
        'password_hash',
        'telefono',
        'cargo',
        'foto_perfil_url',
        'foto_perfil_public_id',
        'estado',
        'email_verified_at',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    protected function casts(): array
    {
        return [
            'id_empresa' => 'integer',
            'id_rol' => 'integer',
            'estado' => 'boolean',
            'email_verified_at' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'password_hash' => 'hashed',
        ];
    }

    public function setCorreoAttribute(string $value): void
    {
        $this->attributes['correo'] = mb_strtolower(trim($value));
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetAccountPassword($token));
    }

    public function routeNotificationForMail($notification = null): string
    {
        return $this->correo;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthIdentifierName(): string
    {
        return 'id_usuario';
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim(implode(' ', array_filter([$this->nombre, $this->apellido])));
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    public function isSuperAdmin(): bool
    {
        return $this->rol?->estado === true && $this->rol?->codigo === 'SUPERADMIN';
    }

    public function isCompanyAdmin(): bool
    {
        return $this->rol?->estado === true && $this->rol?->codigo === 'ADMIN';
    }

    public function hasPermission(string $code): bool
    {
        // Solo el SUPERADMIN de plataforma tiene bypass global.
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->rol || ! $this->rol->estado) {
            return false;
        }

        return $this->rol
            ->permisos()
            ->where('permiso.codigo', $code)
            ->where('permiso.estado', true)
            ->exists();
    }

    /** @param array<int, string> $codes */
    public function hasAnyPermission(array $codes): bool
    {
        foreach ($codes as $code) {
            if ($this->hasPermission($code)) {
                return true;
            }
        }

        return false;
    }
}
