<?php

namespace App\Exports;

use App\Exports\Concerns\DefaultTableStyles;
use App\Filament\Resources\ArticuloResource;
use App\Models\Articulo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;

class ArticulosResumenExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use DefaultTableStyles;

    public function collection()
    {
        return ArticuloResource::getEloquentQuery()
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Articulo',
            'Categoria',
            'Codigo',
            'Total',
            'Principal',
            'Escuelita',
            'Disponibilidad',
            'Estado',
            'En Uso',
            'De Baja',
            'En Reparacion',
            'Extraviado',
            'Bueno',
            'Regular',
            'Malo',
            'Sin Estado',
        ];
    }

    public function map($articulo): array
    {
        /** @var Articulo $articulo */
        return [
            $articulo->nombre,
            $articulo->categoria?->getLabel() ?? (string) $articulo->categoria,
            $articulo->codigo,
            (int) $articulo->items_count,
            (int) $articulo->items_principal_count,
            (int) $articulo->items_escuelita_count,
            ArticuloResource::formatDisponibilidadSummary($articulo),
            ArticuloResource::formatEstadoSummary($articulo),
            (int) $articulo->items_en_uso_count,
            (int) $articulo->items_de_baja_count,
            (int) $articulo->items_en_reparacion_count,
            (int) $articulo->items_extraviado_count,
            (int) $articulo->items_bueno_count,
            (int) $articulo->items_regular_count,
            (int) $articulo->items_malo_count,
            (int) $articulo->items_sin_estado_count,
        ];
    }
}
