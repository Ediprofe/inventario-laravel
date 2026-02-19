<?php

namespace App\Exports\Responsable;

use App\Enums\Disponibilidad;
use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetalleResponsableSheet implements FromArray, WithColumnWidths, WithHeadings, WithStyles, WithTitle
{
    protected int $responsableId;

    protected string $title;

    protected bool $onlyEnUso;

    public function __construct(int $responsableId, string $title, bool $onlyEnUso)
    {
        $this->responsableId = $responsableId;
        $this->title = $title;
        $this->onlyEnUso = $onlyEnUso;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [[
            'Placa',
            'Artículo',
            'Sede',
            'Cód. Ubicación',
            'Ubicación',
            'Disponibilidad',
            'Estado',
            'Marca',
            'Serial',
            'Descripción',
            'Observaciones',
        ]];
    }

    public function array(): array
    {
        $query = Item::query()
            ->where('responsable_id', $this->responsableId)
            ->with(['articulo', 'ubicacion', 'sede'])
            ->orderBy('ubicacion_id')
            ->orderBy('articulo_id');

        if ($this->onlyEnUso) {
            $query->where('disponibilidad', Disponibilidad::EN_USO);
        } else {
            $query->where('disponibilidad', '!=', Disponibilidad::EN_USO);
        }

        $rows = $query->get()->map(function ($item) {
            return [
                $item->placa ?? 'NA',
                $item->articulo->nombre ?? '',
                $item->sede->nombre ?? '',
                $item->ubicacion->codigo ?? '',
                $item->ubicacion->nombre ?? '',
                $item->disponibilidad?->getLabel() ?? '',
                $item->estado?->getLabel() ?? '',
                $item->marca ?? '',
                $item->serial ?? '',
                $item->descripcion ?? '',
                $item->observaciones ?? '',
            ];
        })->values()->toArray();

        if (empty($rows)) {
            return [['Sin registros', '', '', '', '', '', '', '', '', '', '']];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = 'K';

        $headerColor = $this->onlyEnUso ? 'FF15803D' : 'FFB91C1C';
        $headerBorderColor = $this->onlyEnUso ? 'FF14532D' : 'FF7F1D1D';

        $sheet->setShowGridlines(false);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $headerColor]],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $headerBorderColor]],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => $headerBorderColor]],
            ],
        ]);

        if ($highestRow >= 2) {
            $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->applyFromArray([
                'font' => ['size' => 11, 'color' => ['argb' => 'FF111827']],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDBEAFE']],
                ],
            ]);

            for ($row = 2; $row <= $highestRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFF8FAFF');
                }
            }

            $sheet->getStyle("D2:D{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("F2:G{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setAutoFilter("A1:{$highestColumn}{$highestRow}");
        }

        $sheet->freezePane('A2');
        $sheet->setSelectedCell('A1');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 28,
            'C' => 20,
            'D' => 14,
            'E' => 24,
            'F' => 16,
            'G' => 13,
            'H' => 16,
            'I' => 18,
            'J' => 32,
            'K' => 36,
        ];
    }
}
