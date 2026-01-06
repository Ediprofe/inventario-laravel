<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Item;
use App\Exports\Concerns\DefaultTableStyles;

class PorUbicacionExport implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithHeadings
{
    use DefaultTableStyles;

    public function title(): string
    {
        return 'Por Ubicación';
    }

    public function headings(): array
    {
        return ['Sede', 'Código', 'Ubicación', 'Artículo', 'Cantidad'];
    }

    public function array(): array
    {
        return Item::selectRaw('
                sedes.nombre as sede_nombre,
                ubicacions.codigo as ubicacion_codigo,
                ubicacions.nombre as ubicacion_nombre,
                articulos.nombre as articulo_nombre,
                count(*) as cantidad
            ')
            ->join('ubicacions', 'items.ubicacion_id', '=', 'ubicacions.id')
            ->join('sedes', 'items.sede_id', '=', 'sedes.id')
            ->join('articulos', 'items.articulo_id', '=', 'articulos.id')
            ->groupBy('sedes.nombre', 'ubicacions.codigo', 'ubicacions.nombre', 'articulos.nombre')
            ->orderBy('sedes.nombre')
            ->orderBy('ubicacions.codigo')
            ->orderBy('articulos.nombre')
            ->get()
            ->map(fn ($row) => [
                $row->sede_nombre,
                $row->ubicacion_codigo,
                $row->ubicacion_nombre,
                $row->articulo_nombre,
                $row->cantidad,
            ])
            ->toArray();
    }
}
