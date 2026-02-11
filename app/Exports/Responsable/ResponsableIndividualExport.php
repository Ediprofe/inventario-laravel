<?php

namespace App\Exports\Responsable;

use App\Models\Responsable;
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
        $responsable = Responsable::find($this->responsableId);
        $nombre = $responsable ? $responsable->nombre_completo : 'Responsable';

        return [
            new DetalleResponsableSheet($this->responsableId, 'Detalle'),
            new ResumenResponsableSheet($this->responsableId, 'Resumen'),
        ];
    }
}
