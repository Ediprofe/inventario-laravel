<?php

namespace App\Exports\Responsable;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ResponsableIndividualExport implements WithMultipleSheets
{
    protected int $responsableId;

    /** @var array<string,string> */
    protected array $meta;

    /**
     * @param  array<string,string>  $meta
     */
    public function __construct(int $responsableId, array $meta = [])
    {
        $this->responsableId = $responsableId;
        $this->meta = $meta;
    }

    public function sheets(): array
    {
        return [
            new ResumenResponsableSheet($this->responsableId, 'Resumen', $this->meta),
            new DetalleResponsableSheet($this->responsableId, 'Detalle En Uso', true),
            new DetalleResponsableSheet($this->responsableId, 'Detalle No En Uso', false),
        ];
    }
}
