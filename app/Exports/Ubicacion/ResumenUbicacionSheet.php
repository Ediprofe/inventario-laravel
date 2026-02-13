<?php

namespace App\Exports\Ubicacion;

use App\Enums\Disponibilidad;
use App\Models\Item;
use App\Models\Ubicacion;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumenUbicacionSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected int $ubicacionId;
    protected string $title;

    public function __construct(int $ubicacionId, string $title)
    {
        $this->ubicacionId = $ubicacionId;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        $ubicacion = Ubicacion::with(['sede', 'responsable'])->find($this->ubicacionId);

        $items = Item::query()
            ->where('ubicacion_id', $this->ubicacionId)
            ->with('articulo')
            ->get();

        $total = $items->count();
        $enUso = $items->where('disponibilidad', Disponibilidad::EN_USO)->count();
        $fueraUso = $total - $enUso;

        $rows = [];
        $rows[] = ['REPORTE DE INVENTARIO'];
        $rows[] = [sprintf('UBICACIÓN: %s - %s', $ubicacion?->codigo ?? '-', $ubicacion?->nombre ?? '-')];
        $rows[] = [
            sprintf(
                'Sede: %s | Responsable: %s | Generado: %s',
                $ubicacion?->sede?->nombre ?? '-',
                $ubicacion?->responsable?->nombre_completo ?? 'Sin responsable',
                now()->format('Y-m-d H:i')
            ),
        ];
        $rows[] = [''];

        $rows[] = ['Total Ítems', '', 'En Uso', '', 'Fuera de Uso', ''];
        $rows[] = [$total, '', $enUso, '', $fueraUso, ''];
        $rows[] = [''];

        $rows[] = ['Distribución por Artículo (Disponibilidad)'];
        $rows[] = ['Artículo', 'Cant. Total', 'En Uso', 'En Reparación', 'Extraviado', 'De Baja'];

        $grouped = $items
            ->groupBy('articulo_id')
            ->sortBy(fn ($group) => mb_strtolower($group->first()->articulo->nombre ?? ''));

        foreach ($grouped as $group) {
            $rows[] = [
                $group->first()->articulo->nombre ?? 'Sin artículo',
                $group->count(),
                $group->where('disponibilidad', Disponibilidad::EN_USO)->count(),
                $group->where('disponibilidad', Disponibilidad::EN_REPARACION)->count(),
                $group->where('disponibilidad', Disponibilidad::EXTRAVIADO)->count(),
                $group->where('disponibilidad', Disponibilidad::DE_BAJA)->count(),
            ];
        }

        if ($grouped->isEmpty()) {
            $rows[] = ['Sin registros', 0, 0, 0, 0, 0];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = 'F';

        $sheet->setShowGridlines(false);

        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF123A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2:F2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);

        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3:F3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);

        $sheet->mergeCells('A5:B5');
        $sheet->mergeCells('C5:D5');
        $sheet->mergeCells('E5:F5');
        $sheet->mergeCells('A6:B6');
        $sheet->mergeCells('C6:D6');
        $sheet->mergeCells('E6:F6');

        $sheet->getStyle('A5:B5')->applyFromArray($this->cardLabelStyle('FF1E3A8A'));
        $sheet->getStyle('C5:D5')->applyFromArray($this->cardLabelStyle('FF166534'));
        $sheet->getStyle('E5:F5')->applyFromArray($this->cardLabelStyle('FF9A3412'));

        $sheet->getStyle('A6:B6')->applyFromArray($this->cardValueStyle('FFEFF6FF'));
        $sheet->getStyle('C6:D6')->applyFromArray($this->cardValueStyle('FFECFDF5'));
        $sheet->getStyle('E6:F6')->applyFromArray($this->cardValueStyle('FFFFF7ED'));

        $sheet->mergeCells('A8:F8');
        $sheet->getStyle('A8:F8')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A9:F9')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1E40AF']],
            ],
        ]);

        if ($highestRow >= 10) {
            $sheet->getStyle("A10:F{$highestRow}")->applyFromArray([
                'font' => ['size' => 11, 'color' => ['argb' => 'FF0F172A']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDBEAFE']],
                ],
            ]);

            $sheet->getStyle("B10:F{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            for ($row = 10; $row <= $highestRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:F{$row}")
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
            'A' => 36,
            'B' => 14,
            'C' => 14,
            'D' => 14,
            'E' => 12,
            'F' => 12,
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
