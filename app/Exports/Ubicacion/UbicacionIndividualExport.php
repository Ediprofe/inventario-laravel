<?php

namespace App\Exports\Ubicacion;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UbicacionIndividualExport implements WithMultipleSheets
{
    protected int $ubicacionId;
    /** @var array<string,string> */
    protected array $meta;

    /**
     * @param array<string,string> $meta
     */
    public function __construct(int $ubicacionId, array $meta = [])
    {
        $this->ubicacionId = $ubicacionId;
        $this->meta = $meta;
    }

    public function sheets(): array
    {
        return [
            new ResumenUbicacionSheet($this->ubicacionId, 'Resumen', $this->meta),
            new DetalleUbicacionSheet($this->ubicacionId, 'Detalle En Uso', true),
            new DetalleUbicacionSheet($this->ubicacionId, 'Detalle No En Uso', false),
        ];
    }
}
