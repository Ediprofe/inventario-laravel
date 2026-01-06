<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Item;
use App\Models\Sede;

class ResumenExport implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Resumen';
    }

    public function array(): array
    {
        $rows = [];
        
        // Header
        $rows[] = ['RESUMEN GENERAL DE INVENTARIO'];
        $rows[] = ['Fecha de generación: ' . now()->format('d/m/Y H:i')];
        $rows[] = [];
        
        // Totals by Sede
        $rows[] = ['TOTALES POR SEDE'];
        $rows[] = ['Sede', 'Cantidad de Items'];
        foreach (Sede::withCount('items')->get() as $sede) {
            $rows[] = [$sede->nombre, $sede->items_count];
        }
        $rows[] = ['TOTAL', Item::count()];
        $rows[] = [];
        
        // Totals by Estado
        $rows[] = ['TOTALES POR ESTADO FÍSICO'];
        $rows[] = ['Estado', 'Cantidad'];
        $estadoCounts = Item::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        foreach (\App\Enums\EstadoFisico::cases() as $estado) {
            $rows[] = [$estado->getLabel(), $estadoCounts[$estado->value] ?? 0];
        }
        $rows[] = [];
        
        // Totals by Disponibilidad
        $rows[] = ['TOTALES POR DISPONIBILIDAD'];
        $rows[] = ['Disponibilidad', 'Cantidad'];
        $dispCounts = Item::selectRaw('disponibilidad, count(*) as total')
            ->groupBy('disponibilidad')
            ->pluck('total', 'disponibilidad');
        foreach (\App\Enums\Disponibilidad::cases() as $disp) {
            $rows[] = [$disp->getLabel(), $dispCounts[$disp->value] ?? 0];
        }
        
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        // Style the headers
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A5:B5')->getFont()->setBold(true);
        
        return [];
    }
}
