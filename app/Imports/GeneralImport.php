<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GeneralImport implements SkipsUnknownSheets, WithHeadingRow, WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Items' => new SheetImport,
            'Sedes' => new SheetImport,
            'Ubicaciones' => new SheetImport,
            'Articulos' => new SheetImport,
            'Responsables' => new SheetImport,
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Skip unknown sheets
    }
}
