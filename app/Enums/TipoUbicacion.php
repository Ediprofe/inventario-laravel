<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoUbicacion: string implements HasLabel
{
    case AULA = 'aula';
    case LABORATORIO = 'laboratorio';
    case OFICINA = 'oficina';
    case BIBLIOTECA = 'biblioteca';
    case DEPOSITO = 'deposito';
    case AUDITORIO = 'auditorio';
    case SALON_MULTIPLE = 'salon_multiple';

    // Nuevos valores detectados en Excel
    case UNIDAD_SANITARIA = 'unidad_sanitaria';
    case CUARTO_UTIL = 'cuarto_util';
    case SALA = 'sala';
    case INFRAESTRUCTURA = 'infraestructura';
    case APOYO_OPERATIVO = 'apoyo_operativo';

    case OTRO = 'otro';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AULA => 'Aula',
            self::LABORATORIO => 'Laboratorio',
            self::OFICINA => 'Oficina',
            self::BIBLIOTECA => 'Biblioteca',
            self::DEPOSITO => 'Depósito',
            self::AUDITORIO => 'Auditorio',
            self::SALON_MULTIPLE => 'Salón Múltiple',
            self::UNIDAD_SANITARIA => 'Unidad Sanitaria',
            self::CUARTO_UTIL => 'Cuarto Útil',
            self::SALA => 'Sala',
            self::INFRAESTRUCTURA => 'Infraestructura',
            self::APOYO_OPERATIVO => 'Apoyo Operativo',
            self::OTRO => 'Otro',
        };
    }
}
