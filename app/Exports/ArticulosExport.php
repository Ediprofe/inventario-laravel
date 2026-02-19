<?php

namespace App\Exports;

use App\Exports\Concerns\DefaultTableStyles;
use App\Models\Articulo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArticulosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use DefaultTableStyles;

    public function collection()
    {
        return Articulo::all();
    }

    public function headings(): array
    {
        return [
            'Nombre*',
            'Categoría*',
            'Código',
            'Descripción',
        ];
    }

    public function map($articulo): array
    {
        return [
            $articulo->nombre,
            $articulo->categoria->getLabel(),
            $articulo->codigo,
            $articulo->descripcion,
        ];
    }

    public function title(): string
    {
        return 'Articulos';
    }
}
