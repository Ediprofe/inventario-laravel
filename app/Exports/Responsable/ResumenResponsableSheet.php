<?php

namespace App\Exports\Responsable;

use App\Enums\Disponibilidad;
use App\Models\Item;
use App\Models\Responsable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumenResponsableSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected int $responsableId;
    protected string $title;

    public function __construct(int $responsableId, string $title)
    {
        $this->responsableId = $responsableId;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        $responsable = Responsable::find($this->responsableId);

        $items = Item::query()
            ->where('responsable_id', $this->responsableId)
            ->with(['articulo', 'ubicacion'])
            ->orderBy('ubicacion_id')
            ->orderBy('articulo_id')
            ->get();

        $total = $items->count();
        $enUso = $items->where('disponibilidad', Disponibilidad::EN_USO)->count();
        $fueraUso = $total - $enUso;

        $rows = [];
        $rows[] = ['REPORTE DE INVENTARIO'];
        $rows[] = [sprintf('RESPONSABLE: %s', $responsable?->nombre_completo ?? '-')];
        $rows[] = [
            sprintf(
                'Cargo: %s | Email: %s | Generado: %s',
                $responsable?->cargo ?? '-',
                $responsable?->email ?? '-',
                now()->format('Y-m-d H:i')
            ),
        ];
        $rows[] = [''];

        $rows[] = ['Total Ítems', '', '', 'En Uso', '', '', 'Fuera de Uso', ''];
        $rows[] = [$total, '', '', $enUso, '', '', $fueraUso, ''];
        $rows[] = [''];

        $rows[] = ['Distribución por Ubicación y Artículo (Disponibilidad)'];
        $rows[] = ['Cód. Ubicación', 'Ubicación', 'Artículo', 'Cant. Total', 'En Uso', 'En Reparación', 'Extraviado', 'De Baja'];

        $grouped = $items
            ->groupBy(fn ($item) => $item->ubicacion_id . '_' . $item->articulo_id)
            ->sortBy(function ($group) {
                $first = $group->first();
                return mb_strtolower(($first->ubicacion->codigo ?? '') . ' ' . ($first->articulo->nombre ?? ''));
            });

        foreach ($grouped as $group) {
            $first = $group->first();
            $rows[] = [
                $first->ubicacion->codigo ?? '-',
                $first->ubicacion->nombre ?? '-',
                $first->articulo->nombre ?? 'Sin artículo',
                $group->count(),
                $group->where('disponibilidad', Disponibilidad::EN_USO)->count(),
                $group->where('disponibilidad', Disponibilidad::EN_REPARACION)->count(),
                $group->where('disponibilidad', Disponibilidad::EXTRAVIADO)->count(),
                $group->where('disponibilidad', Disponibilidad::DE_BAJA)->count(),
            ];
        }

        if ($grouped->isEmpty()) {
            $rows[] = ['Sin registros', '', '', 0, 0, 0, 0, 0];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();

        $sheet->setShowGridlines(false);

        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF123A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);

        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);

        $sheet->mergeCells('A5:C5');
        $sheet->mergeCells('D5:F5');
        $sheet->mergeCells('G5:H5');
        $sheet->mergeCells('A6:C6');
        $sheet->mergeCells('D6:F6');
        $sheet->mergeCells('G6:H6');

        $sheet->getStyle('A5:C5')->applyFromArray($this->cardLabelStyle('FF1E3A8A'));
        $sheet->getStyle('D5:F5')->applyFromArray($this->cardLabelStyle('FF166534'));
        $sheet->getStyle('G5:H5')->applyFromArray($this->cardLabelStyle('FF9A3412'));

        $sheet->getStyle('A6:C6')->applyFromArray($this->cardValueStyle('FFEFF6FF'));
        $sheet->getStyle('D6:F6')->applyFromArray($this->cardValueStyle('FFECFDF5'));
        $sheet->getStyle('G6:H6')->applyFromArray($this->cardValueStyle('FFFFF7ED'));

        $sheet->mergeCells('A8:H8');
        $sheet->getStyle('A8:H8')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A9:H9')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1E40AF']],
            ],
        ]);

        if ($highestRow >= 10) {
            $sheet->getStyle("A10:H{$highestRow}")->applyFromArray([
                'font' => ['size' => 11, 'color' => ['argb' => 'FF0F172A']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDBEAFE']],
                ],
            ]);

            $sheet->getStyle("D10:H{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            for ($row = 10; $row <= $highestRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:H{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFF8FAFF');
                }
            }
        }

        // No AutoFilter in executive sheet to keep it visually clean.
        $sheet->setSelectedCell('A1');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 26,
            'C' => 30,
            'D' => 13,
            'E' => 12,
            'F' => 14,
            'G' => 12,
            'H' => 12,
        ];
    }

    private function cardLabelStyle(string $bgColor): array
    {
        return [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1E3A8A']],
            ],
        ];
    }

    private function cardValueStyle(string $bgColor): array
    {
        return [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']],
            ],
        ];
    }
}
