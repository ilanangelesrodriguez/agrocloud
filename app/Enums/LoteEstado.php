<?php

namespace App\Enums;

enum LoteEstado: string
{
    case RECIBIDO = 'RECIBIDO';
    case EN_MUESTREO = 'EN_MUESTREO';
    case EN_INSPECCION = 'EN_INSPECCION';
    case PENDIENTE_VALIDACION = 'PENDIENTE_VALIDACION';
    case VALIDADO = 'VALIDADO';
    case CLASIFICADO = 'CLASIFICADO';
    case OBSERVADO = 'OBSERVADO';
    case RECHAZADO = 'RECHAZADO';

    public function label(): string
    {
        return match ($this) {
            self::RECIBIDO => 'Recibido',
            self::EN_MUESTREO => 'En muestreo',
            self::EN_INSPECCION => 'En inspección',
            self::PENDIENTE_VALIDACION => 'Pendiente de validación',
            self::VALIDADO => 'Validado',
            self::CLASIFICADO => 'Clasificado',
            self::OBSERVADO => 'Observado',
            self::RECHAZADO => 'Rechazado',
        };
    }
}