<?php

namespace App\Exports\Ubicacion;

use App\Models\Item;
use App\Exports\Concerns\DefaultTableStyles;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DetalleUbicacionSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithHeadings
{
    use DefaultTableStyles;

    protected $ubicacionId;
    protected $title;

    public function __construct(int $ubicacionId, string $title)
    {
        $this->ubicacionId = $ubicacionId;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [
            ['Placa', 'Artículo', 'Responsable', 'Marca', 'Serial', 'Estado', 'Disponibilidad', 'Descripción', 'Observaciones']
        ];
    }

    public function array(): array
    {
        return Item::query()
            ->where('ubicacion_id', $this->ubicacionId)
            ->with(['articulo', 'responsable'])
            ->orderBy('articulo_id')
            ->get()
            ->map(function ($item) {
                return [
                    $item->placa ?? 'NA',
                    $item->articulo->nombre ?? '',
                    $item->responsable->nombre_completo ?? '',
                    $item->marca ?? '',
                    $item->serial ?? '',
                    $item->estado?->getLabel() ?? '',
                    $item->disponibilidad?->getLabel() ?? '',
                    $item->descripcion ?? '',
                    $item->observaciones ?? '',
                ];
            })
            ->toArray();
    }

}
