<?php

namespace App\Exports\Responsable;

use App\Enums\Disponibilidad;
use App\Enums\EstadoFisico;
use App\Models\Item;
use App\Exports\Concerns\DefaultTableStyles;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

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
            [
                'Cod. Ubicacion',
                'Ubicacion',
                'Articulo',
                'Cantidad Total',
                'En Uso',
                'En Reparacion',
                'Extraviado',
                'De Baja',
                'Bueno',
                'Regular',
                'Malo',
                'Sin Estado',
            ],
        ];
    }

    public function array(): array
    {
        return Item::query()
            ->where('responsable_id', $this->responsableId)
            ->with(['articulo', 'ubicacion'])
            ->orderBy('ubicacion_id')
            ->orderBy('articulo_id')
            ->get()
            ->groupBy(fn ($item) => $item->ubicacion_id . '_' . $item->articulo_id)
            ->map(function ($group) {
                $first = $group->first();

                $disponibilidadCounts = [];
                foreach (Disponibilidad::cases() as $disponibilidad) {
                    $disponibilidadCounts[$disponibilidad->value] = $group->where('disponibilidad', $disponibilidad)->count();
                }

                $estadoCounts = [];
                foreach (EstadoFisico::cases() as $estado) {
                    $estadoCounts[$estado->value] = $group->where('estado', $estado)->count();
                }

                return [
                    $first->ubicacion->codigo ?? '',
                    $first->ubicacion->nombre ?? '',
                    $first->articulo->nombre ?? '',
                    $group->count(),
                    $disponibilidadCounts[Disponibilidad::EN_USO->value] ?? 0,
                    $disponibilidadCounts[Disponibilidad::EN_REPARACION->value] ?? 0,
                    $disponibilidadCounts[Disponibilidad::EXTRAVIADO->value] ?? 0,
                    $disponibilidadCounts[Disponibilidad::DE_BAJA->value] ?? 0,
                    $estadoCounts[EstadoFisico::BUENO->value] ?? 0,
                    $estadoCounts[EstadoFisico::REGULAR->value] ?? 0,
                    $estadoCounts[EstadoFisico::MALO->value] ?? 0,
                    $estadoCounts[EstadoFisico::SIN_ESTADO->value] ?? 0,
                ];
            })
            ->values()
            ->toArray();
    }
}
