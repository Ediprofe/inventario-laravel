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
            ['Placa', 'Artículo', 'Sede', 'Cód. Ubicación', 'Ubicación', 'Responsable', 'Estado']
        ];
    }

    public function array(): array
    {
        return Item::enUso()
            ->where('ubicacion_id', $this->ubicacionId)
            ->with(['articulo', 'sede', 'ubicacion', 'responsable'])
            ->orderBy('articulo_id')
            ->get()
            ->map(function ($item) {
                return [
                    $item->placa ?? 'NA',
                    $item->articulo->nombre ?? '',
                    $item->sede->nombre ?? '',
                    $item->ubicacion->codigo ?? '',
                    $item->ubicacion->nombre ?? '',
                    $item->responsable->nombre_completo ?? '',
                    $item->estado?->getLabel() ?? '',
                ];
            })
            ->toArray();
    }
}
