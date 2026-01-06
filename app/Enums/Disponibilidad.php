<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum Disponibilidad: string implements HasLabel, HasColor
{
    case EN_USO = 'en_uso';
    case EN_REPARACION = 'en_reparacion';
    case EXTRAVIADO = 'extraviado';
    case DE_BAJA = 'de_baja';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EN_USO => 'En Uso',
            self::EN_REPARACION => 'En Reparación',
            self::EXTRAVIADO => 'Extraviado',
            self::DE_BAJA => 'De Baja',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::EN_USO => 'success',
            self::EN_REPARACION => 'warning',
            self::EXTRAVIADO => 'danger',
            self::DE_BAJA => 'gray',
        };
    }
}
