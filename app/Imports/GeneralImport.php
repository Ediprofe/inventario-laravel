<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class GeneralImport implements WithMultipleSheets, WithHeadingRow, SkipsUnknownSheets
{
    public function sheets(): array
    {
        return [
            'Items' => new SheetImport(),
            'Sedes' => new SheetImport(),
            'Ubicaciones' => new SheetImport(),
            'Articulos' => new SheetImport(),
            'Responsables' => new SheetImport(),
        ];
    }
    
    public function onUnknownSheet($sheetName)
    {
        // Skip unknown sheets
    }
}
