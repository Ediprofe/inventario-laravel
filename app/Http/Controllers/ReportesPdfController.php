<?php

namespace App\Http\Controllers;

use App\Mail\InventarioReportMail;
use App\Services\InventarioReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
     * Send inventory PDF by email to the responsible person
     */
    public function enviarUbicacion(int $ubicacionId)
    {
        $data = $this->reportService->getInventarioPorUbicacion($ubicacionId);
        
        if (!$data['ubicacion']) {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada'
            ], 404);
        }
        
        $responsable = $data['ubicacion']->responsable;
        
        if (!$responsable) {
            return response()->json([
                'success' => false,
                'message' => 'Esta ubicación no tiene un responsable asignado'
            ], 422);
        }
        
        if (!$responsable->email) {
            return response()->json([
                'success' => false,
                'message' => "El responsable {$responsable->nombre_completo} no tiene email registrado"
            ], 422);
        }
        
        // Generate PDF to temporary file
        $pdf = Pdf::loadView('pdf.ubicacion', compact('data'));
        $filename = 'Inventario_' . $data['ubicacion']->codigo . '_' . now()->format('Y-m-d') . '.pdf';
        $tempPath = storage_path('app/temp/' . $filename);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $pdf->save($tempPath);
        
        try {
            Mail::to($responsable->email)
                ->send(new InventarioReportMail(
                    destinatario: $responsable->nombre_completo,
                    tipoReporte: 'Inventario por Ubicación',
                    nombreReporte: $data['ubicacion']->nombre,
                    archivoPath: $tempPath,
                    archivoNombre: $filename
                ));
            
            // Clean up temp file
            @unlink($tempPath);
            
            return response()->json([
                'success' => true,
                'message' => "Reporte enviado exitosamente a {$responsable->email}"
            ]);
        } catch (\Exception $e) {
            // Clean up temp file on error
            @unlink($tempPath);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }
}

