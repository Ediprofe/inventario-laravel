<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SheetImport implements ToArray, WithHeadingRow
{
    public function array(array $array)
    {
        // No-op, we access data via Excel::toArray()
    }
}
