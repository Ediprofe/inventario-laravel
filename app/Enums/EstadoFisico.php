<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoFisico: string implements HasColor, HasLabel
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
