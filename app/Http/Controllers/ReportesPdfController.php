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
}
