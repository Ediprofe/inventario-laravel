<?php

namespace App\Exports\Ubicacion;

use App\Models\Ubicacion;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UbicacionIndividualExport implements WithMultipleSheets
{
    protected $ubicacionId;

    public function __construct(int $ubicacionId)
    {
        $this->ubicacionId = $ubicacionId;
    }

    public function sheets(): array
    {
        return [
            new DetalleUbicacionSheet($this->ubicacionId, 'Detalle'),
            new ResumenUbicacionSheet($this->ubicacionId, 'Resumen'),
        ];
    }
}
