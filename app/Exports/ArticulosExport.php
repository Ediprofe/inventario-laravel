<?php

namespace App\Exports;

use App\Models\Articulo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArticulosExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
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
