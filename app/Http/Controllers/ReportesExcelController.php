<?php

namespace App\Http\Controllers;

use App\Exports\Responsable\ResponsableIndividualExport;
use App\Exports\Ubicacion\UbicacionIndividualExport;
use App\Mail\InventarioReportMail;
use App\Models\Responsable;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportesExcelController extends Controller
{
    /**
     * Generate Excel for inventory by location
     */
    public function ubicacion(int $ubicacionId)
    {
        $ubicacion = Ubicacion::findOrFail($ubicacionId);
        
        $filename = 'Inventario_' . $ubicacion->codigo . '_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new UbicacionIndividualExport($ubicacionId), $filename);
    }

    public function responsable(int $responsableId)
    {
        $responsable = Responsable::findOrFail($responsableId);
        
        $filename = 'Inventario_' . str_replace(' ', '_', $responsable->nombre_completo) . '_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new ResponsableIndividualExport($responsableId), $filename);
    }

    /**
     * Send inventory Excel by email to the responsible person
     */
    public function enviarResponsable(int $responsableId)
    {
        $responsable = Responsable::find($responsableId);
        
        if (!$responsable) {
            return response()->json([
                'success' => false,
                'message' => 'Responsable no encontrado'
            ], 404);
        }
        
        if (!$responsable->email) {
            return response()->json([
                'success' => false,
                'message' => "El responsable {$responsable->nombre_completo} no tiene email registrado"
            ], 422);
        }
        
        // Generate Excel to temporary file
        $filename = 'Inventario_' . str_replace(' ', '_', $responsable->nombre_completo) . '_' . date('Y-m-d') . '.xlsx';
        
        $localDisk = Storage::disk('local');
        if (!$localDisk->exists('temp')) {
            $localDisk->makeDirectory('temp');
        }
        
        Excel::store(new ResponsableIndividualExport($responsableId), 'temp/' . $filename, 'local');
        $tempPath = $localDisk->path('temp/' . $filename);
        
        try {
            Mail::to($responsable->email)
                ->send(new InventarioReportMail(
                    destinatario: $responsable->nombre_completo,
                    tipoReporte: 'Inventario por Responsable',
                    nombreReporte: $responsable->nombre_completo,
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

