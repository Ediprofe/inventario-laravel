<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Database\Eloquent\Builder;

use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use App\Exports\Concerns\DefaultTableStyles;

class ItemsExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize, WithColumnWidths
{
    use DefaultTableStyles;

    public function columnWidths(): array
    {
        return [
            'J' => 50, // Descripción
            'K' => 50, // Observaciones
        ];
    }

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Item::query()->with(['sede', 'ubicacion', 'articulo', 'responsable']);
        
        // Apply filters if passed (e.g. from Filament)
        // Note: Filament filters are often complex. 
        // We might just export ALL items for the "Backup" purpose if filters are empty or if strictly requested "Backup".
        // The implementation_plan says "Export maintains same format".
        // If $filters provided, apply them.
        
        return $query;
    }

    public function headings(): array
    {
        return [
            'Sede (Nombre)*',
            'Ubicacion (Nombre)*',
            'Articulo (Nombre)*',
            'Responsable (Nombre Completo)',
            'Placa',
            'Marca',
            'Serial',
            'Estado Físico*',
            'Disponibilidad*',
            'Descripción',
            'Observaciones',
        ];
    }

    public function map($item): array
    {
        return [
            $item->sede->nombre,
            $item->ubicacion->nombre,
            $item->articulo->nombre,
            $item->responsable?->nombre_completo,
            $item->placa,
            $item->marca,
            $item->serial,
            $item->estado->getLabel(), // Label or value? Excel usually input uses text. Label is safer for humans. Import logic checks both.
            $item->disponibilidad->getLabel(),
            $item->descripcion,
            $item->observaciones,
        ];
    }

    public function title(): string
    {
        return 'Items';
    }
}
