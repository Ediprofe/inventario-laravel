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
    /** @var array<string,string> */
    protected array $meta;
    protected int $tableDataStartRow = 10;
    protected int $tableDataEndRow = 10;
    protected int $observacionesTitleRow = 0;
    protected int $observacionesBodyRow = 0;
    protected string $observacionesText = '';

    /**
     * @param array<string,string> $meta
     */
    public function __construct(int $ubicacionId, string $title, array $meta = [])
    {
        $this->ubicacionId = $ubicacionId;
        $this->title = $title;
        $this->meta = $meta;
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
                'Sede: %s | Responsable: %s | Generado: %s%s%s%s',
                $ubicacion?->sede?->nombre ?? '-',
                $ubicacion?->responsable?->nombre_completo ?? 'Sin responsable',
                now()->format('Y-m-d H:i'),
                !empty($this->meta['codigo_envio']) ? ' | Envío: ' : '',
                $this->meta['codigo_envio'] ?? '',
                !empty($this->meta['firmante_responsable']) ? ' | Firma: ' . $this->meta['firmante_responsable'] : ''
            ),
        ];
        $rows[] = [''];

        $rows[] = ['Total Ítems', '', 'En Uso', '', 'Fuera de Uso', '']; // row 5
        $rows[] = [$total, '', $enUso, '', $fueraUso, '']; // row 6
        $rows[] = [''];

        $rows[] = ['Distribución por Artículo y Disponibilidad']; // row 8
        $rows[] = ['Artículo', 'Cant. Total', 'En Uso', 'En Reparación', 'Extraviado', 'De Baja']; // row 9

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

        $this->tableDataEndRow = count($rows);

        $observacion = trim((string) ($ubicacion?->observaciones ?? ''));
        $this->observacionesText = $observacion !== '' ? $observacion : 'Sin observaciones registradas.';

        $rows[] = [''];
        $this->observacionesTitleRow = count($rows) + 1;
        $rows[] = ['Observaciones de la ubicación'];
        $this->observacionesBodyRow = count($rows) + 1;
        $rows[] = [$this->observacionesText];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
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
        $sheet->getStyle('C9')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF14532D']]],
        ]);
        $sheet->getStyle('D9:F9')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFB91C1C']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF7F1D1D']]],
        ]);

        if ($this->tableDataEndRow >= $this->tableDataStartRow) {
            $sheet->getStyle("A{$this->tableDataStartRow}:F{$this->tableDataEndRow}")->applyFromArray([
                'font' => ['size' => 11, 'color' => ['argb' => 'FF0F172A']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDBEAFE']],
                ],
            ]);

            $sheet->getStyle("B{$this->tableDataStartRow}:F{$this->tableDataEndRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            for ($row = $this->tableDataStartRow; $row <= $this->tableDataEndRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:F{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFF8FAFF');
                }
            }
        }

        if ($this->observacionesTitleRow > 0 && $this->observacionesBodyRow > 0) {
            $sheet->mergeCells("A{$this->observacionesTitleRow}:F{$this->observacionesTitleRow}");
            $sheet->mergeCells("A{$this->observacionesBodyRow}:F{$this->observacionesBodyRow}");

            $sheet->getStyle("A{$this->observacionesTitleRow}:F{$this->observacionesTitleRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E3A8A']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFDBFE']]],
            ]);

            $sheet->getStyle("A{$this->observacionesBodyRow}:F{$this->observacionesBodyRow}")->applyFromArray([
                'font' => ['size' => 10, 'color' => ['argb' => 'FF0F172A']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
            ]);

            $estimatedLines = max(
                3,
                substr_count($this->observacionesText, "\n") + 1,
                (int) ceil(mb_strlen($this->observacionesText) / 110)
            );
            $sheet->getRowDimension($this->observacionesBodyRow)->setRowHeight($estimatedLines * 16);
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
