<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait DefaultTableStyles
{
    public function styles(Worksheet $sheet)
    {
        // 1. Get highest column and row to define the range
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $range = 'A1:' . $highestColumn . $highestRow;
        $headerRange = 'A1:' . $highestColumn . '1';
        $bodyRange = $highestRow > 1 ? 'A2:' . $highestColumn . $highestRow : null;

        // Enable AutoFilter
        $sheet->setAutoFilter($range);

        // Freeze Top Row
        $sheet->freezePane('A2');

        // Premium-like table look
        $sheet->setShowGridlines(false);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Header: Strong blue background with white bold text
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0F4BCF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF0A2A74'],
                ],
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF0A2A74'],
                ],
            ],
        ];

        // Body: light borders and better readability
        $bodyStyle = [
            'font' => [
                'size' => 11,
                'color' => ['argb' => 'FF111827'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD6E3F5'],
                ],
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

        // Apply styles directly and reset selection to a single cell.
        $sheet->getStyle($headerRange)->applyFromArray($headerStyle);

        if ($bodyRange !== null) {
            $sheet->getStyle($bodyRange)->applyFromArray($bodyStyle);

            // Zebra rows for a cleaner "premium" table feel.
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
