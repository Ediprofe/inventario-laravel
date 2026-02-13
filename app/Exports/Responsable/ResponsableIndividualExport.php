<?php

namespace App\Exports\Responsable;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ResponsableIndividualExport implements WithMultipleSheets
{
    protected $responsableId;

    public function __construct(int $responsableId)
    {
        $this->responsableId = $responsableId;
    }

    public function sheets(): array
    {
        return [
            new ResumenResponsableSheet($this->responsableId, 'Resumen Ejecutivo'),
            new DetalleResponsableSheet($this->responsableId, 'Detalle En Uso', true),
            new DetalleResponsableSheet($this->responsableId, 'Detalle No En Uso', false),
        ];
    }
}
