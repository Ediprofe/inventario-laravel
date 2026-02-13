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
            new ResumenUbicacionSheet($this->ubicacionId, 'Resumen Ejecutivo'),
            new DetalleUbicacionSheet($this->ubicacionId, 'Detalle En Uso', true),
            new DetalleUbicacionSheet($this->ubicacionId, 'Detalle No En Uso', false),
        ];
    }
}
