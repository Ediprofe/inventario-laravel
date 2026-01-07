<?php

namespace App\Exports\Responsable;

use App\Models\Item;
use App\Models\Responsable;
use App\Exports\Concerns\DefaultTableStyles;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Enums\Disponibilidad;

class ResumenResponsableSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithHeadings
{
    // Do NOT use default trait if we want custom styles for row 5
    // use DefaultTableStyles; 

    protected $responsableId;
    protected $title;

    public function __construct(int $responsableId, string $title)
    {
        $this->responsableId = $responsableId;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE INVENTARIO - RESUMEN'],
            ['Responsable:', $this->getResponsableName()],
            ['Fecha:', date('Y-m-d H:i')],
            [],
            ['Cód. Ubicación', 'Ubicación', 'Artículo', 'Cantidad']
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Title Style (Row 1)
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Metadata (Rows 2-3)
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);
        
        // Table Header Style (Row 5)
        $sheet->getStyle('5')->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4B5563'); // Gray-600
        
        // Borders for table (Row 5 onwards)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A5:D' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // AutoFilter starting from Row 5
        $sheet->setAutoFilter('A5:D' . $lastRow);
        
        return [];
    }

    protected function getResponsableName()
    {
        return Responsable::find($this->responsableId)?->nombre_completo ?? 'N/A';
    }

    public function array(): array
    {
        return Item::enUso()
            ->where('responsable_id', $this->responsableId)
            ->selectRaw('articulo_id, ubicacion_id, count(*) as total')
            ->with(['articulo', 'ubicacion'])
            ->groupBy('articulo_id', 'ubicacion_id')
            ->orderBy('ubicacion_id') // Group visually by location
            ->get()
            ->map(function ($row) {
                return [
                    $row->ubicacion->codigo ?? '',
                    $row->ubicacion->nombre ?? '?',
                    $row->articulo->nombre ?? '?',
                    $row->total,
                ];
            })
            ->toArray();
    }
}
