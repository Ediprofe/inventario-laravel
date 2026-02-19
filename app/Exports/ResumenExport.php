<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\Sede;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumenExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Resumen';
    }

    public function array(): array
    {
        $rows = [];

        // Header
        $rows[] = ['RESUMEN GENERAL DE INVENTARIO'];
        $rows[] = ['Fecha de generación: '.now()->format('d/m/Y H:i')];
        $rows[] = [];

        // Totals by Sede
        $rows[] = ['TOTALES POR SEDE'];
        $rows[] = ['Sede', 'Cantidad de Items'];
        foreach (Sede::withCount('items')->get() as $sede) {
            $rows[] = [$sede->nombre, $sede->items_count];
        }
        $rows[] = ['TOTAL', Item::count()];
        $rows[] = [];

        // Totals by Estado
        $rows[] = ['TOTALES POR ESTADO FÍSICO'];
        $rows[] = ['Estado', 'Cantidad'];
        $estadoCounts = Item::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        foreach (\App\Enums\EstadoFisico::cases() as $estado) {
            $rows[] = [$estado->getLabel(), $estadoCounts[$estado->value] ?? 0];
        }
        $rows[] = [];

        // Totals by Disponibilidad
        $rows[] = ['TOTALES POR DISPONIBILIDAD'];
        $rows[] = ['Disponibilidad', 'Cantidad'];
        $dispCounts = Item::selectRaw('disponibilidad, count(*) as total')
            ->groupBy('disponibilidad')
            ->pluck('total', 'disponibilidad');
        foreach (\App\Enums\Disponibilidad::cases() as $disp) {
            $rows[] = [$disp->getLabel(), $dispCounts[$disp->value] ?? 0];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        // Resumen Title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Define header rows for each section
        $headerRows = [5, 12, 23]; // Adjust these based on where the tables start

        // Default Header Style (Gray 600 Background, White Text)
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4B5563'], // Tailwind Gray-600
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        // Apply style to known header rows cells
        // Note: The specific row numbers might shift if content above changes dynamically,
        // but for this fixed structure:
        // Row 5 is Sede Table Header
        $sheet->getStyle('A5:B5')->applyFromArray($headerStyle);

        // Find dynamically where the other tables start to apply styles correctly
        // Or simply search for cells containing "Estado" or "Disponibilidad"

        // Helper to find and style headers dynamically
        foreach ($sheet->getRowIterator() as $row) {
            $cellValue = (string) $sheet->getCell('A'.$row->getRowIndex())->getValue();

            // Checking exact table headers
            if ($cellValue === 'Sede' || $cellValue === 'Estado' || $cellValue === 'Disponibilidad') {
                $sheet->getStyle('A'.$row->getRowIndex().':B'.$row->getRowIndex())->applyFromArray($headerStyle);
            }
        }

        return [];
    }
}
