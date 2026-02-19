<?php

namespace App\Exports;

use App\Exports\Concerns\DefaultTableStyles;
use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsolidadoArticulosExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    use DefaultTableStyles;

    public function title(): string
    {
        return 'Por Artículo';
    }

    public function headings(): array
    {
        return [
            'Artículo',
            'Sede',
            'Cantidad Total',
            'En Uso',
            'En Reparación',
            'Extraviado',
            'De Baja',
        ];
    }

    public function array(): array
    {
        return Item::query()
            ->selectRaw('
                articulos.nombre as articulo_nombre,
                sedes.nombre as sede_nombre,
                count(*) as total,
                sum(case when items.disponibilidad = ? then 1 else 0 end) as en_uso,
                sum(case when items.disponibilidad = ? then 1 else 0 end) as en_reparacion,
                sum(case when items.disponibilidad = ? then 1 else 0 end) as extraviado,
                sum(case when items.disponibilidad = ? then 1 else 0 end) as de_baja
            ', ['en_uso', 'en_reparacion', 'extraviado', 'de_baja'])
            ->join('articulos', 'items.articulo_id', '=', 'articulos.id')
            ->join('sedes', 'items.sede_id', '=', 'sedes.id')
            ->groupBy('articulos.nombre', 'sedes.nombre')
            ->orderBy('articulos.nombre')
            ->orderBy('sedes.nombre')
            ->get()
            ->map(fn ($row) => [
                $row->articulo_nombre,
                $row->sede_nombre,
                (int) $row->total,
                (int) $row->en_uso,
                (int) $row->en_reparacion,
                (int) $row->extraviado,
                (int) $row->de_baja,
            ])
            ->toArray();
    }

    public function styles(Worksheet $sheet)
    {
        $this->applyDefaultTableStyles($sheet);

        $highestRow = $sheet->getHighestRow();

        // Header semantics: green for En Uso, red for non-use columns.
        $sheet->getStyle('D1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF15803D');
        $sheet->getStyle('D1')->getBorders()->getAllBorders()->getColor()->setARGB('FF14532D');

        $sheet->getStyle('E1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFB91C1C');
        $sheet->getStyle('E1:G1')->getBorders()->getAllBorders()->getColor()->setARGB('FF7F1D1D');

        // Body cues: positive En Uso in green tint, positive non-use in red tint.
        for ($row = 2; $row <= $highestRow; $row++) {
            $enUso = (int) $sheet->getCell("D{$row}")->getValue();
            if ($enUso > 0) {
                $sheet->getStyle("D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCFCE7');
            }

            foreach (['E', 'F', 'G'] as $col) {
                $value = (int) $sheet->getCell("{$col}{$row}")->getValue();
                if ($value > 0) {
                    $sheet->getStyle("{$col}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEE2E2');
                }
            }
        }

        return [];
    }
}
