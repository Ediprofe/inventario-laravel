<?php

namespace App\Http\Controllers;

use App\Mail\InventarioReportMail;
use App\Models\EnvioInventario;
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
        $data = $this->reportService->getInventarioPorUbicacionCompleto($ubicacionId);
        
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
        $data = $this->reportService->getInventarioPorResponsableCompleto($responsableId);
        
        if (!$data['responsable']) {
            abort(404, 'Responsable no encontrado');
        }
        
        $pdf = Pdf::loadView('pdf.responsable', compact('data'));
        
        $nombreLimpio = str_replace(' ', '_', $data['responsable']->nombre_completo);
        $filename = 'Inventario_' . $nombreLimpio . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Send inventory PDF by email — by location
     */
    public function enviarUbicacion(int $ubicacionId)
    {
        $data = $this->reportService->getInventarioPorUbicacionCompleto($ubicacionId);
        
        if (!$data['ubicacion']) {
            return response()->json(['success' => false, 'message' => 'Ubicación no encontrada'], 404);
        }
        
        $responsable = $data['ubicacion']->responsable;
        
        if (!$responsable) {
            return response()->json(['success' => false, 'message' => 'Esta ubicación no tiene un responsable asignado'], 422);
        }
        
        if (!$responsable->email) {
            return response()->json(['success' => false, 'message' => "El responsable {$responsable->nombre_completo} no tiene email registrado"], 422);
        }
        
        // Create envío record
        $envio = EnvioInventario::create([
            'responsable_id' => $responsable->id,
            'tipo' => 'por_ubicacion',
            'ubicacion_id' => $ubicacionId,
            'email_enviado_a' => $responsable->email,
            'enviado_at' => now(),
            'token' => EnvioInventario::generarToken(),
        ]);
        
        $urlAprobacion = url("/inventario/aprobar/{$envio->token}");
        
        // Generate PDF to temporary file
        $pdf = Pdf::loadView('pdf.ubicacion', compact('data'));
        $filename = 'Inventario_' . $data['ubicacion']->codigo . '_' . now()->format('Y-m-d') . '.pdf';
        $tempPath = storage_path('app/temp/' . $filename);
        
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
                    archivoNombre: $filename,
                    urlAprobacion: $urlAprobacion
                ));
            
            @unlink($tempPath);
            
            return response()->json([
                'success' => true,
                'message' => "Reporte enviado a {$responsable->email}"
            ]);
        } catch (\Exception $e) {
            @unlink($tempPath);
            $envio->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send inventory PDF by email — by responsible person
     */
    public function enviarResponsable(int $responsableId)
    {
        $data = $this->reportService->getInventarioPorResponsableCompleto($responsableId);
        
        if (!$data['responsable']) {
            return response()->json(['success' => false, 'message' => 'Responsable no encontrado'], 404);
        }
        
        $responsable = $data['responsable'];
        
        if (!$responsable->email) {
            return response()->json(['success' => false, 'message' => "El responsable {$responsable->nombre_completo} no tiene email registrado"], 422);
        }
        
        // Create envío record
        $envio = EnvioInventario::create([
            'responsable_id' => $responsable->id,
            'tipo' => 'por_responsable',
            'ubicacion_id' => null,
            'email_enviado_a' => $responsable->email,
            'enviado_at' => now(),
            'token' => EnvioInventario::generarToken(),
        ]);
        
        $urlAprobacion = url("/inventario/aprobar/{$envio->token}");
        
        // Generate PDF
        $pdf = Pdf::loadView('pdf.responsable', compact('data'));
        $nombreLimpio = str_replace(' ', '_', $responsable->nombre_completo);
        $filename = 'Inventario_' . $nombreLimpio . '_' . now()->format('Y-m-d') . '.pdf';
        $tempPath = storage_path('app/temp/' . $filename);
        
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $pdf->save($tempPath);
        
        try {
            Mail::to($responsable->email)
                ->send(new InventarioReportMail(
                    destinatario: $responsable->nombre_completo,
                    tipoReporte: 'Inventario por Responsable',
                    nombreReporte: $responsable->nombre_completo,
                    archivoPath: $tempPath,
                    archivoNombre: $filename,
                    urlAprobacion: $urlAprobacion
                ));
            
            @unlink($tempPath);
            
            return response()->json([
                'success' => true,
                'message' => "Reporte enviado a {$responsable->email}"
            ]);
        } catch (\Exception $e) {
            @unlink($tempPath);
            $envio->delete();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }
}
