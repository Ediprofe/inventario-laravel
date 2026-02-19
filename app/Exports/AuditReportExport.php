<?php

namespace App\Exports;

use App\Enums\Disponibilidad;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AuditReportExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ItemsExport(
                filters: ['disponibilidad' => Disponibilidad::EN_USO],
                sheetTitle: 'Items'
            ),
            new ItemsExport(
                filters: ['disponibilidad_not' => Disponibilidad::EN_USO],
                sheetTitle: 'Items en no uso'
            ),
            new SedesExport,
            new UbicacionesExport,
            new ArticulosExport,
            new ResponsablesExport,
            new PorUbicacionExport,
            new PorResponsableExport,
            new ConsolidadoArticulosExport,
            new EnviosInventarioExport,
        ];
    }
}
