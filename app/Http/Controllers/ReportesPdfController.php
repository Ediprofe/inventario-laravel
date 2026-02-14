<?php

namespace App\Http\Controllers;

use App\Exports\Responsable\ResponsableIndividualExport;
use App\Exports\Ubicacion\UbicacionIndividualExport;
use App\Mail\InventarioReportMail;
use App\Models\EnvioInventario;
use App\Services\InventarioReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportesPdfController extends Controller
{
    protected InventarioReportService $reportService;

    public function __construct(InventarioReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    protected function buildApprovalUrl(string $token): string
    {
        $baseUrl = rtrim((string) config('app.public_url', config('app.url')), '/');

        return $baseUrl . "/inventario/aprobar/{$token}";
    }

    /**
     * Generate PDF for inventory by location (summary only, no detail)
     */
    public function ubicacion(int $ubicacionId)
    {
        $data = $this->reportService->getInventarioPorUbicacion($ubicacionId);
        
        if (!$data['ubicacion']) {
            abort(404, 'Ubicación no encontrada');
        }
        
        $pdf = Pdf::loadView('pdf.ubicacion', compact('data'));
        
        $nombreReporte = $data['ubicacion']->codigo . ' - ' . $data['ubicacion']->nombre;
        $filename = 'Inventario_' . str_replace(['/', ' '], '_', $nombreReporte) . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Generate PDF for inventory by responsible person (summary only)
     */
    public function responsable(int $responsableId)
    {
        $data = $this->reportService->getInventarioPorResponsable($responsableId);
        
        if (!$data['responsable']) {
            abort(404, 'Responsable no encontrado');
        }
        
        $pdf = Pdf::loadView('pdf.responsable', compact('data'));
        
        $nombreLimpio = str_replace(' ', '_', $data['responsable']->nombre_completo);
        $filename = 'Inventario_' . $nombreLimpio . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Send inventory PDF + Excel by email — by location
     */
    public function enviarUbicacion(int $ubicacionId)
    {
        $data = $this->reportService->getInventarioPorUbicacion($ubicacionId);
        
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
        
        $urlAprobacion = $this->buildApprovalUrl($envio->token);
        
        // Ensure temp directory exists in local disk
        $localDisk = Storage::disk('local');
        if (!$localDisk->exists('temp')) {
            $localDisk->makeDirectory('temp');
        }
        
        $codigoUbicacion = $data['ubicacion']->codigo;
        $fecha = now()->format('Y-m-d');
        
        // Generate PDF (summary only)
        $pdf = Pdf::loadView('pdf.ubicacion', compact('data'));
        $pdfFilename = "Inventario_{$codigoUbicacion}_{$fecha}.pdf";
        $pdfPath = $localDisk->path('temp/' . $pdfFilename);
        $pdf->save($pdfPath);
        
        // Generate Excel (resumen + detalle sheets)
        $excelFilename = "Inventario_{$codigoUbicacion}_{$fecha}.xlsx";
        Excel::store(new UbicacionIndividualExport($ubicacionId), 'temp/' . $excelFilename, 'local');
        $excelPath = $localDisk->path('temp/' . $excelFilename);
        
        try {
            Mail::to($responsable->email)
                ->send(new InventarioReportMail(
                    destinatario: $responsable->nombre_completo,
                    tipoReporte: 'Inventario por Ubicación',
                    nombreReporte: $data['ubicacion']->codigo . ' - ' . $data['ubicacion']->nombre,
                    archivoPath: [$pdfPath, $excelPath],
                    archivoNombre: [$pdfFilename, $excelFilename],
                    urlAprobacion: $urlAprobacion
                ));
            
            @unlink($pdfPath);
            @unlink($excelPath);
            
            return response()->json([
                'success' => true,
                'message' => "Reporte enviado a {$responsable->email}"
            ]);
        } catch (\Exception $e) {
            @unlink($pdfPath);
            @unlink($excelPath);
            $envio->delete();
            Log::error('Error enviando reporte por ubicacion', [
                'ubicacion_id' => $ubicacionId,
                'responsable_id' => $responsable->id,
                'email' => $responsable->email,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send inventory PDF + Excel by email — by responsible person
     */
    public function enviarResponsable(int $responsableId)
    {
        $data = $this->reportService->getInventarioPorResponsable($responsableId);
        
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
        
        $urlAprobacion = $this->buildApprovalUrl($envio->token);
        
        // Ensure temp directory exists in local disk
        $localDisk = Storage::disk('local');
        if (!$localDisk->exists('temp')) {
            $localDisk->makeDirectory('temp');
        }
        
        $nombreLimpio = str_replace(' ', '_', $responsable->nombre_completo);
        $fecha = now()->format('Y-m-d');
        
        // Generate PDF (summary only)
        $pdf = Pdf::loadView('pdf.responsable', compact('data'));
        $pdfFilename = "Inventario_{$nombreLimpio}_{$fecha}.pdf";
        $pdfPath = $localDisk->path('temp/' . $pdfFilename);
        $pdf->save($pdfPath);
        
        // Generate Excel (resumen + detalle sheets)
        $excelFilename = "Inventario_{$nombreLimpio}_{$fecha}.xlsx";
        Excel::store(new ResponsableIndividualExport($responsableId), 'temp/' . $excelFilename, 'local');
        $excelPath = $localDisk->path('temp/' . $excelFilename);
        
        try {
            Mail::to($responsable->email)
                ->send(new InventarioReportMail(
                    destinatario: $responsable->nombre_completo,
                    tipoReporte: 'Inventario por Responsable',
                    nombreReporte: $responsable->nombre_completo,
                    archivoPath: [$pdfPath, $excelPath],
                    archivoNombre: [$pdfFilename, $excelFilename],
                    urlAprobacion: $urlAprobacion
                ));
            
            @unlink($pdfPath);
            @unlink($excelPath);
            
            return response()->json([
                'success' => true,
                'message' => "Reporte enviado a {$responsable->email}"
            ]);
        } catch (\Exception $e) {
            @unlink($pdfPath);
            @unlink($excelPath);
            $envio->delete();
            Log::error('Error enviando reporte por responsable', [
                'responsable_id' => $responsableId,
                'email' => $responsable->email,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }
}
