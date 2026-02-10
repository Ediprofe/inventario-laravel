<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AuditReportExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ItemsExport([]),
            new SedesExport(),
            new UbicacionesExport(),
            new ArticulosExport(),
            new ResponsablesExport(),
            new PorUbicacionExport(),
            new PorResponsableExport(),
            new EnviosInventarioExport(),
        ];
    }
}
