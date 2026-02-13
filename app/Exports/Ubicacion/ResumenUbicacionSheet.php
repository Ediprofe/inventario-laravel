<?php

namespace App\Exports\Ubicacion;

use App\Enums\Disponibilidad;
use App\Enums\EstadoFisico;
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

    protected int $rowDisponibilidadHeader = 0;
    protected int $rowEstadoHeader = 0;
    protected int $rowDistribucionTitle = 0;
    protected int $rowDistribucionHeader = 0;

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
        $noEnUso = $total - $enUso;

        $rows = [];

        $rows[] = ['REPORTE DE INVENTARIO POR UBICACIÓN'];
        $rows[] = ['Generado', now()->format('Y-m-d H:i')];
        $rows[] = ['Sede', $ubicacion?->sede?->nombre ?? '-'];
        $rows[] = ['Ubicación', trim(($ubicacion?->codigo ?? '-') . ' - ' . ($ubicacion?->nombre ?? '-'))];
        $rows[] = ['Responsable', $ubicacion?->responsable?->nombre_completo ?? 'Sin responsable'];
        $rows[] = ['Totales', "Total: {$total} | En uso: {$enUso} | No en uso: {$noEnUso}"];
        $rows[] = [];

        $this->rowDisponibilidadHeader = count($rows) + 1;
        $rows[] = ['Totales por Disponibilidad', 'Cantidad'];
        foreach (Disponibilidad::cases() as $disponibilidad) {
            $rows[] = [
                $disponibilidad->getLabel(),
                $items->where('disponibilidad', $disponibilidad)->count(),
            ];
        }

        $rows[] = [];

        $this->rowEstadoHeader = count($rows) + 1;
        $rows[] = ['Totales por Estado', 'Cantidad'];
        foreach (EstadoFisico::cases() as $estado) {
            $rows[] = [
                $estado->getLabel(),
                $items->where('estado', $estado)->count(),
            ];
        }

        $rows[] = [];

        $this->rowDistribucionTitle = count($rows) + 1;
        $rows[] = ['Distribución por Artículo'];

        $this->rowDistribucionHeader = count($rows) + 1;
        $rows[] = ['Artículo', 'Cantidad Total', 'En Uso', 'No En Uso', 'De Baja', 'Bueno', 'Regular', 'Malo', 'Sin Estado'];

        $grouped = $items
            ->groupBy('articulo_id')
            ->sortBy(fn ($group) => mb_strtolower($group->first()->articulo->nombre ?? ''));

        foreach ($grouped as $group) {
            $totalArticulo = $group->count();
            $enUsoArticulo = $group->where('disponibilidad', Disponibilidad::EN_USO)->count();

            $rows[] = [
                $group->first()->articulo->nombre ?? 'Sin artículo',
                $totalArticulo,
                $enUsoArticulo,
                $totalArticulo - $enUsoArticulo,
                $group->where('disponibilidad', Disponibilidad::DE_BAJA)->count(),
                $group->where('estado', EstadoFisico::BUENO)->count(),
                $group->where('estado', EstadoFisico::REGULAR)->count(),
                $group->where('estado', EstadoFisico::MALO)->count(),
                $group->where('estado', EstadoFisico::SIN_ESTADO)->count(),
            ];
        }

        if ($grouped->isEmpty()) {
            $rows[] = ['Sin registros', 0, 0, 0, 0, 0, 0, 0, 0];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = 'I';

        $sheet->setShowGridlines(false);
        $sheet->mergeCells("A1:{$highestColumn}1");
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF123A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A2:A6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']],
            ],
        ]);
        $sheet->getStyle('B2:B6')->applyFromArray([
            'font' => ['color' => ['argb' => 'FF0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']],
            ],
        ]);

        $this->styleMiniTable($sheet, $this->rowDisponibilidadHeader, Disponibilidad::cases());
        $this->styleMiniTable($sheet, $this->rowEstadoHeader, EstadoFisico::cases());

        $sheet->mergeCells("A{$this->rowDistribucionTitle}:{$highestColumn}{$this->rowDistribucionTitle}");
        $sheet->getStyle("A{$this->rowDistribucionTitle}:{$highestColumn}{$this->rowDistribucionTitle}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("A{$this->rowDistribucionHeader}:{$highestColumn}{$this->rowDistribucionHeader}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1E40AF']],
            ],
        ]);

        $firstDataRow = $this->rowDistribucionHeader + 1;
        if ($highestRow >= $firstDataRow) {
            $sheet->getStyle("A{$firstDataRow}:{$highestColumn}{$highestRow}")->applyFromArray([
                'font' => ['size' => 11, 'color' => ['argb' => 'FF0F172A']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDBEAFE']],
                ],
            ]);

            for ($row = $firstDataRow; $row <= $highestRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFF8FAFF');
                }
            }

            $sheet->getStyle("B{$firstDataRow}:I{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->setAutoFilter("A{$this->rowDistribucionHeader}:{$highestColumn}{$highestRow}");
            $sheet->freezePane('A' . ($this->rowDistribucionHeader + 1));
        }

        $sheet->setSelectedCell('A1');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 36,
            'B' => 24,
            'C' => 13,
            'D' => 13,
            'E' => 11,
            'F' => 11,
            'G' => 11,
            'H' => 11,
            'I' => 13,
        ];
    }

    private function styleMiniTable(Worksheet $sheet, int $headerRow, array $cases): void
    {
        $endRow = $headerRow + count($cases);

        $sheet->getStyle("A{$headerRow}:B{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1E3A8A']],
            ],
        ]);

        $sheet->getStyle("A" . ($headerRow + 1) . ":B{$endRow}")->applyFromArray([
            'font' => ['size' => 11, 'color' => ['argb' => 'FF0F172A']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDBEAFE']],
            ],
        ]);

        for ($row = $headerRow + 1; $row <= $endRow; $row++) {
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:B{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFF8FAFF');
            }
        }

        $sheet->getStyle("B" . ($headerRow + 1) . ":B{$endRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}
