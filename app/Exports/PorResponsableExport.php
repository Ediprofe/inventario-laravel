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

class PorResponsableExport implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithHeadings
{
    use DefaultTableStyles;

    public function title(): string
    {
        return 'Por Responsable';
    }

    public function headings(): array
    {
        return ['Responsable', 'Artículo', 'Cantidad', 'Ubicación', 'Cód. Ubicación'];
    }

    public function array(): array
    {
        return Item::selectRaw('
                COALESCE(responsables.nombre, \'Sin nombre\') || \' \' || COALESCE(responsables.apellido, \'\') as responsable_nombre,
                articulos.nombre as articulo_nombre,
                count(*) as cantidad,
                ubicacions.nombre as ubicacion_nombre,
                ubicacions.codigo as ubicacion_codigo
            ')
            ->leftJoin('responsables', 'items.responsable_id', '=', 'responsables.id')
            ->join('articulos', 'items.articulo_id', '=', 'articulos.id')
            ->join('ubicacions', 'items.ubicacion_id', '=', 'ubicacions.id')
            ->where('items.disponibilidad', \App\Enums\Disponibilidad::EN_USO)
            ->groupBy('responsable_nombre', 'articulos.nombre', 'ubicacions.nombre', 'ubicacions.codigo')
            ->orderBy('responsable_nombre')
            ->orderBy('articulos.nombre')
            ->orderBy('ubicacions.nombre')
            ->get()
            ->map(fn ($row) => [
                $row->responsable_nombre ?: 'Sin Asignar',
                $row->articulo_nombre,
                $row->cantidad,
                $row->ubicacion_nombre,
                $row->ubicacion_codigo,
            ])
            ->toArray();
    }
}
