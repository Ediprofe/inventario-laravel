<?php

namespace App\Exports\Ubicacion;

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

class DetalleUbicacionSheet implements FromArray, WithTitle, WithStyles, WithHeadings, WithColumnWidths
{
    protected int $ubicacionId;
    protected string $title;
    protected bool $onlyEnUso;

    public function __construct(int $ubicacionId, string $title, bool $onlyEnUso)
    {
        $this->ubicacionId = $ubicacionId;
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
            'Responsable',
            'Marca',
            'Serial',
            'Disponibilidad',
            'Estado',
            'Descripción',
            'Observaciones',
        ]];
    }

    public function array(): array
    {
        $query = Item::query()
            ->where('ubicacion_id', $this->ubicacionId)
            ->with(['articulo', 'responsable'])
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
                $item->responsable->nombre_completo ?? '',
                $item->marca ?? '',
                $item->serial ?? '',
                $item->disponibilidad?->getLabel() ?? '',
                $item->estado?->getLabel() ?? '',
                $item->descripcion ?? '',
                $item->observaciones ?? '',
            ];
        })->values()->toArray();

        if (empty($rows)) {
            return [['Sin registros', '', '', '', '', '', '', '', '']];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = 'I';

        $headerColor = $this->onlyEnUso ? 'FF1D4ED8' : 'FF9A3412';
        $headerBorderColor = $this->onlyEnUso ? 'FF1E3A8A' : 'FF7C2D12';

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

            $sheet->getStyle("F2:F{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("G2:G{$highestRow}")
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
            'C' => 28,
            'D' => 16,
            'E' => 18,
            'F' => 16,
            'G' => 13,
            'H' => 34,
            'I' => 40,
        ];
    }
}
