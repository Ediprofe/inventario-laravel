<?php

namespace App\Exports\Ubicacion;

use App\Models\Item;
use App\Models\Ubicacion;
use App\Enums\EstadoFisico;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ResumenUbicacionSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithHeadings
{
    protected $ubicacionId;
    protected $title;

    public function __construct(int $ubicacionId, string $title)
    {
        $this->ubicacionId = $ubicacionId;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [
            ['Artículo', 'Cantidad', 'Bueno', 'Regular', 'Malo']
        ];
    }

    public function array(): array
    {
        $items = Item::enUso()
            ->where('ubicacion_id', $this->ubicacionId)
            ->with('articulo')
            ->get();

        // Agrupar por artículo y contar por estado
        return $items->groupBy('articulo_id')->map(function ($group) {
            $articulo = $group->first()->articulo;
            $total = $group->count();
            
            $bueno = $group->where('estado', EstadoFisico::BUENO)->count();
            $regular = $group->where('estado', EstadoFisico::REGULAR)->count();
            $malo = $group->where('estado', EstadoFisico::MALO)->count();

            return [
                $articulo->nombre ?? '',
                $total,
                $bueno ?: '',
                $regular ?: '',
                $malo ?: '',
            ];
        })->values()->toArray();
    }

    public function styles(Worksheet $sheet)
    {
        $ubicacion = Ubicacion::with(['sede', 'responsable'])->find($this->ubicacionId);
        $observaciones = $ubicacion?->observaciones;

        // Get data dimensions
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        // --- Add observaciones BELOW the data table ---
        if ($observaciones) {
            $obsStartRow = $highestRow + 2; // Leave one blank row

            $sheet->setCellValue("A{$obsStartRow}", '📝 Observaciones de la Ubicación:');
            $sheet->getStyle("A{$obsStartRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => 'FF92400E'],
                ],
            ]);

            $obsContentRow = $obsStartRow + 1;
            $sheet->setCellValue("A{$obsContentRow}", $observaciones);
            $sheet->mergeCells("A{$obsContentRow}:E{$obsContentRow}");
            $sheet->getStyle("A{$obsContentRow}")->applyFromArray([
                'font' => ['size' => 11, 'color' => ['argb' => 'FF78350F']],
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFBEB'],
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFFDE68A'],
                    ],
                ],
            ]);
            $sheet->getRowDimension($obsContentRow)->setRowHeight(-1); // Auto height
        }

        // --- Standard table styles (header + body + zebra) ---
        $range = 'A1:' . $highestColumn . $highestRow;
        $headerRange = 'A1:' . $highestColumn . '1';
        $bodyRange = $highestRow > 1 ? 'A2:' . $highestColumn . $highestRow : null;

        $sheet->setAutoFilter($range);
        $sheet->freezePane('A2');
        $sheet->setShowGridlines(false);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0F4BCF'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF0A2A74']],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF0A2A74']],
            ],
        ];

        $bodyStyle = [
            'font' => ['size' => 11, 'color' => ['argb' => 'FF111827']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD6E3F5']],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'wrapText' => true,
            ],
        ];

        $stripeStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF5FAFF'],
            ],
        ];

        $sheet->getStyle($headerRange)->applyFromArray($headerStyle);

        if ($bodyRange !== null) {
            $sheet->getStyle($bodyRange)->applyFromArray($bodyStyle);
            for ($row = 2; $row <= $highestRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray($stripeStyle);
                }
            }
        }

        $sheet->setSelectedCell('A1');

        return [];
    }
}
