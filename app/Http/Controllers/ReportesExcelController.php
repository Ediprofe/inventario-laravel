<?php

namespace App\Http\Controllers;

use App\Exports\Responsable\ResponsableIndividualExport;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportesExcelController extends Controller
{
    public function responsable(int $responsableId)
    {
        $responsable = Responsable::findOrFail($responsableId);
        
        $filename = 'Inventario_' . str_replace(' ', '_', $responsable->nombre_completo) . '_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new ResponsableIndividualExport($responsableId), $filename);
    }
}
