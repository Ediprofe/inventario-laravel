<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InventoryExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new ItemsExport($this->filters),
            new SedesExport,
            new UbicacionesExport,
            new ArticulosExport,
            new ResponsablesExport,
            new EnviosInventarioExport,
        ];
    }
}
