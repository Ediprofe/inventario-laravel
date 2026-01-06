<?php

namespace App\Exports;

use App\Models\Sede;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class SedesExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function collection()
    {
        return Sede::with('coordinador')->get();
    }

    public function headings(): array
    {
        return [
            'Nombre*',
            'Código*',
            'Coordinador',
            'Dirección',
            'Teléfono',
            'Email',
        ];
    }

    public function map($sede): array
    {
        return [
            $sede->nombre,
            $sede->codigo,
            $sede->coordinador?->nombre_completo,
            $sede->direccion,
            $sede->telefono,
            $sede->email,
        ];
    }

    public function title(): string
    {
        return 'Sedes';
    }
}
