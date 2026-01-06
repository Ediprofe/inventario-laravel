<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum EstadoFisico: string implements HasLabel, HasColor
{
    case BUENO = 'bueno';
    case REGULAR = 'regular';
    case MALO = 'malo';
    case SIN_ESTADO = 'sin_estado';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BUENO => 'Bueno',
            self::REGULAR => 'Regular',
            self::MALO => 'Malo',
            self::SIN_ESTADO => 'Sin Estado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BUENO => 'success',
            self::REGULAR => 'warning',
            self::MALO => 'danger',
            self::SIN_ESTADO => 'gray',
        };
    }
}
