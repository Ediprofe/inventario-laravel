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
    public function __construct(int $responsableId, string $title, array $meta = [])
    {
        $this->responsableId = $responsableId;
        $this->title = $title;
        $this->meta = $meta;
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
                'Cargo: %s | Email: %s | Generado: %s%s%s%s',
                $responsable?->cargo ?? '-',
                $responsable?->email ?? '-',
                now()->format('Y-m-d H:i'),
                !empty($this->meta['codigo_envio']) ? ' | Envío: ' : '',
                $this->meta['codigo_envio'] ?? '',
                !empty($this->meta['firmante_responsable']) ? ' | Firma: ' . $this->meta['firmante_responsable'] : ''
            ),
        ];
        $rows[] = [''];

        $observaciones = $items->pluck('ubicacion')
            ->filter()
            ->unique('id')
            ->filter(fn ($ubicacion) => filled($ubicacion->observaciones))
            ->map(fn ($ubicacion) => sprintf(
                "- %s - %s:\n%s",
                $ubicacion->codigo ?? '-',
                $ubicacion->nombre ?? 'Ubicación',
                trim((string) $ubicacion->observaciones)
            ))
            ->implode("\n\n");

        $this->observacionesText = $observaciones !== '' ? $observaciones : 'Sin observaciones registradas.';

        $rows[] = ['Total Ítems', '', '', 'En Uso', '', '', 'Fuera de Uso', '']; // row 5
        $rows[] = [$total, '', '', $enUso, '', '', $fueraUso, '']; // row 6
        $rows[] = [''];

        $rows[] = ['Distribución por Ubicación, Artículo y Disponibilidad']; // row 8
        $rows[] = ['Cód. Ubicación', 'Ubicación', 'Artículo', 'Cant. Total', 'En Uso', 'En Reparación', 'Extraviado', 'De Baja']; // row 9

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

        $this->tableDataEndRow = count($rows);
        $rows[] = [''];
        $this->observacionesTitleRow = count($rows) + 1;
        $rows[] = ['Observaciones de ubicaciones'];
        $this->observacionesBodyRow = count($rows) + 1;
        $rows[] = [$this->observacionesText];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
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
        $sheet->getStyle('E9')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF14532D']]],
        ]);
        $sheet->getStyle('F9:H9')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFB91C1C']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF7F1D1D']]],
        ]);

        if ($this->tableDataEndRow >= $this->tableDataStartRow) {
            $sheet->getStyle("A{$this->tableDataStartRow}:H{$this->tableDataEndRow}")->applyFromArray([
                'font' => ['size' => 11, 'color' => ['argb' => 'FF0F172A']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDBEAFE']],
                ],
            ]);

            $sheet->getStyle("D{$this->tableDataStartRow}:H{$this->tableDataEndRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            for ($row = $this->tableDataStartRow; $row <= $this->tableDataEndRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:H{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFF8FAFF');
                }
            }
        }

        if ($this->observacionesTitleRow > 0 && $this->observacionesBodyRow > 0) {
            $sheet->mergeCells("A{$this->observacionesTitleRow}:H{$this->observacionesTitleRow}");
            $sheet->mergeCells("A{$this->observacionesBodyRow}:H{$this->observacionesBodyRow}");

            $sheet->getStyle("A{$this->observacionesTitleRow}:H{$this->observacionesTitleRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E3A8A']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFDBFE']]],
            ]);

            $sheet->getStyle("A{$this->observacionesBodyRow}:H{$this->observacionesBodyRow}")->applyFromArray([
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
                (int) ceil(mb_strlen($this->observacionesText) / 140)
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
