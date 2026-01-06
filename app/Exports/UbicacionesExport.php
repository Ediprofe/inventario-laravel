<?php

namespace App\Exports;

use App\Models\Ubicacion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class UbicacionesExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function collection()
    {
        return Ubicacion::with(['sede', 'responsable'])->get();
    }

    public function headings(): array
    {
        return [
            'Sede (Nombre)*',
            'Nombre*',
            'Código*',
            'Tipo*',
            'Responsable Por Defecto',
            'Piso',
            'Capacidad',
            'Observaciones',
        ];
    }

    public function map($ubicacion): array
    {
        return [
            $ubicacion->sede->nombre,
            $ubicacion->nombre,
            $ubicacion->codigo,
            $ubicacion->tipo->getLabel(),
            $ubicacion->responsable?->nombre_completo,
            $ubicacion->piso,
            $ubicacion->capacidad,
            $ubicacion->observaciones,
        ];
    }

    public function title(): string
    {
        return 'Ubicaciones';
    }
}
