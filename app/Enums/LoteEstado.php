<?php

namespace App\Enums;

enum LoteEstado: string
{
    case PENDIENTE_RECEPCION = 'PENDIENTE_RECEPCION';
    case RECIBIDO = 'RECIBIDO';
    case EN_MUESTREO = 'EN_MUESTREO';
    case EN_INSPECCION = 'EN_INSPECCION';
    case PENDIENTE_VALIDACION = 'PENDIENTE_VALIDACION';
    case VALIDADO = 'VALIDADO';
    case CLASIFICADO = 'CLASIFICADO';
    case OBSERVADO = 'OBSERVADO';
    case RETENIDO = 'RETENIDO';
    case RECHAZADO = 'RECHAZADO';
    case ANULADO = 'ANULADO';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE_RECEPCION => 'Pendiente de recepción',
            self::RECIBIDO => 'Recibido',
            self::EN_MUESTREO => 'En muestreo',
            self::EN_INSPECCION => 'En inspección',
            // Se conserva el código existente por compatibilidad con lotes guardados.
            self::PENDIENTE_VALIDACION => 'Pendiente de dictamen del supervisor',
            self::VALIDADO => 'Validado',
            self::CLASIFICADO => 'Aprobado para producción',
            self::OBSERVADO => 'Observado',
            self::RETENIDO => 'Retenido por incidencia',
            self::RECHAZADO => 'Rechazado',
            self::ANULADO => 'Anulado',
        };
    }
}
