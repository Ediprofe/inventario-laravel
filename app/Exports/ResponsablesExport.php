<?php

namespace App\Exports;

use App\Models\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ResponsablesExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function collection()
    {
        return Responsable::with('sede')->get();
    }

    public function headings(): array
    {
        return [
            'Nombre Completo*',
            'Tipo Documento',
            'Documento',
            'Cargo',
            'Email',
            'Teléfono',
            'Sede (Nombre)',
        ];
    }

    public function map($responsable): array
    {
        return [
            $responsable->nombre_completo ?? ($responsable->nombre . ' ' . $responsable->apellido),
            $responsable->tipo_documento,
            $responsable->documento,
            $responsable->cargo,
            $responsable->email,
            $responsable->telefono,
            $responsable->sede?->nombre,
        ];
    }

    public function title(): string
    {
        return 'Responsables';
    }
}
