<?php

namespace App\Http\Controllers;

use App\Services\InventarioReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportesPdfController extends Controller
{
    protected InventarioReportService $reportService;

    public function __construct(InventarioReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate PDF for inventory by location
     */
    public function ubicacion(int $ubicacionId)
    {
        $data = $this->reportService->getInventarioPorUbicacion($ubicacionId);
        
        if (!$data['ubicacion']) {
            abort(404, 'Ubicación no encontrada');
        }
        
        $pdf = Pdf::loadView('pdf.ubicacion', compact('data'));
        
        $filename = 'Inventario_' . $data['ubicacion']->codigo . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Generate PDF for inventory by responsible person
     */
    public function responsable(int $responsableId)
    {
        $data = $this->reportService->getInventarioPorResponsable($responsableId);
        
        if (!$data['responsable']) {
            abort(404, 'Responsable no encontrado');
        }
        
        $pdf = Pdf::loadView('pdf.responsable', compact('data'));
        
        $filename = 'Inventario_' . str_replace(' ', '_', $data['responsable']->nombre_completo) . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}
