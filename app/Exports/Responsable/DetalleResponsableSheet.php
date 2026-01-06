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

class DetalleResponsableSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithHeadings
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
        // Same order as ItemResource::table(), without "Responsable"
        return [
            ['Placa', 'Artículo', 'Sede', 'Cód. Ubicación', 'Ubicación', 'Marca', 'Serial', 'Estado', 'Disponibilidad', 'Descripción', 'Observaciones']
        ];
    }

    public function array(): array
    {
        return Item::where('responsable_id', $this->responsableId)
            ->where('disponibilidad', \App\Enums\Disponibilidad::EN_USO)
            ->with(['articulo', 'ubicacion', 'sede'])
            ->get()
            ->map(function ($item) {
                return [
                    $item->placa,
                    $item->articulo->nombre ?? '',
                    $item->sede->nombre ?? '',
                    $item->ubicacion->codigo ?? '',
                    $item->ubicacion->nombre ?? '',
                    $item->marca,
                    $item->serial,
                    $item->estado?->getLabel() ?? '',
                    $item->disponibilidad?->getLabel() ?? '',
                    $item->descripcion,
                    $item->observaciones,
                ];
            })
            ->toArray();
    }
}
