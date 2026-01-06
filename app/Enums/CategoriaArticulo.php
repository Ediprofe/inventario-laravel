<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CategoriaArticulo: string implements HasLabel
{
    case TECNOLOGIA = 'tecnologia';
    case MOBILIARIO = 'mobiliario';
    case LABORATORIO = 'laboratorio';
    case DEPORTES = 'deportes';
    case AUDIOVISUAL = 'audiovisual';
    case LIBROS = 'libros';
    case HERRAMIENTAS = 'herramientas';
    case VEHICULOS = 'vehiculos';
    case OTROS = 'otros';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TECNOLOGIA => 'Tecnología',
            self::MOBILIARIO => 'Mobiliario',
            self::LABORATORIO => 'Laboratorio',
            self::DEPORTES => 'Deportes',
            self::AUDIOVISUAL => 'Audiovisual',
            self::LIBROS => 'Libros',
            self::HERRAMIENTAS => 'Herramientas',
            self::VEHICULOS => 'Vehículos',
            self::OTROS => 'Otros',
        };
    }
}
