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
    use DefaultTableStyles; 

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
            ['Cód. Ubicación', 'Ubicación', 'Artículo', 'Cantidad']
        ];
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
