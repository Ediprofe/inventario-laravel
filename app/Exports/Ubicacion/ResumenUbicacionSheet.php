<?php

namespace App\Exports\Ubicacion;

use App\Models\Item;
use App\Models\Ubicacion;
use App\Exports\Concerns\DefaultTableStyles;
use App\Enums\EstadoFisico;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResumenUbicacionSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithHeadings
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
            ['Artículo', 'Cantidad', 'Bueno', 'Regular', 'Malo']
        ];
    }

    public function array(): array
    {
        $items = Item::enUso()
            ->where('ubicacion_id', $this->ubicacionId)
            ->with('articulo')
            ->get();

        // Agrupar por artículo y contar por estado
        return $items->groupBy('articulo_id')->map(function ($group) {
            $articulo = $group->first()->articulo;
            $total = $group->count();
            
            $bueno = $group->where('estado', EstadoFisico::BUENO)->count();
            $regular = $group->where('estado', EstadoFisico::REGULAR)->count();
            $malo = $group->where('estado', EstadoFisico::MALO)->count();

            return [
                $articulo->nombre ?? '',
                $total,
                $bueno ?: '',
                $regular ?: '',
                $malo ?: '',
            ];
        })->values()->toArray();
    }
}
