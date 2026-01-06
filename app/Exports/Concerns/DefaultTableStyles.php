<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

trait DefaultTableStyles
{
    public function styles(Worksheet $sheet)
    {
        // 1. Get highest column and row to define the range
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $range = 'A1:' . $highestColumn . $highestRow;

        // Enable AutoFilter
        $sheet->setAutoFilter($range);
        
        // Freeze Top Row
        $sheet->freezePane('A2');

        return [
            // Header: Bold, Gray Background, White Text
            1 => [
                'font' => [
                    'bold' => true, 
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4B5563'], // Tailwind Gray-600 roughly
                ],
            ],

            // Body: Borders, Vertical Center
            $range => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true, // Enable wrap text
                ],
            ],
        ];
    }
}
