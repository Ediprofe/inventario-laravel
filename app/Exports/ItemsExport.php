<?php

namespace App\Exports;

use App\Exports\Concerns\DefaultTableStyles;
use App\Models\Item;
use BackedEnum;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

class ItemsExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithHeadings, WithMapping, WithStyles, WithTitle
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
    protected string $sheetTitle;

    public function __construct(array $filters = [], string $sheetTitle = 'Items')
    {
        $this->filters = $filters;
        $this->sheetTitle = $sheetTitle;
    }

    public function query()
    {
        $query = Item::query()
            ->with(['sede', 'ubicacion', 'articulo', 'responsable'])
            ->orderBy('updated_at', 'desc');

        if (array_key_exists('disponibilidad', $this->filters)) {
            $query->where('disponibilidad', $this->normalizeFilterValue($this->filters['disponibilidad']));
        }

        if (array_key_exists('disponibilidad_not', $this->filters)) {
            $query->where('disponibilidad', '!=', $this->normalizeFilterValue($this->filters['disponibilidad_not']));
        }

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
        return $this->sheetTitle;
    }

    protected function getTableHeaderFillColor(): string
    {
        return array_key_exists('disponibilidad_not', $this->filters)
            ? 'FFB91C1C'
            : 'FF0F4BCF';
    }

    protected function getTableHeaderBorderColor(): string
    {
        return array_key_exists('disponibilidad_not', $this->filters)
            ? 'FF7F1D1D'
            : 'FF0A2A74';
    }

    protected function normalizeFilterValue(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }
}
