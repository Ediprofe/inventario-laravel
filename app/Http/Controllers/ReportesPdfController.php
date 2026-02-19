<?php

namespace App\Http\Controllers;

use App\Models\Responsable;
use App\Models\Ubicacion;
use App\Services\InventarioFirmaEnvioService;
use App\Services\InventarioReportService;
use App\Support\DompdfRuntimeConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ReportesPdfController extends Controller
{
    protected InventarioReportService $reportService;

    protected InventarioFirmaEnvioService $firmaEnvioService;

    public function __construct(
        InventarioReportService $reportService,
        InventarioFirmaEnvioService $firmaEnvioService,
    ) {
        $this->reportService = $reportService;
        $this->firmaEnvioService = $firmaEnvioService;
    }

    /**
     * Generate PDF for inventory by location (summary only, no detail)
     */
    public function ubicacion(int $ubicacionId)
    {
        $data = $this->reportService->getInventarioPorUbicacion($ubicacionId);

        if (! $data['ubicacion']) {
            abort(404, 'Ubicación no encontrada');
        }

        DompdfRuntimeConfig::apply();
        $pdf = Pdf::loadView('pdf.ubicacion', compact('data'));

        $nombreReporte = $data['ubicacion']->codigo.' - '.$data['ubicacion']->nombre;
        $filename = 'Inventario_'.str_replace(['/', ' '], '_', $nombreReporte).'_'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate PDF for inventory by responsible person (summary only)
     */
    public function responsable(int $responsableId)
    {
        $data = $this->reportService->getInventarioPorResponsable($responsableId);

        if (! $data['responsable']) {
            abort(404, 'Responsable no encontrado');
        }

        DompdfRuntimeConfig::apply();
        $pdf = Pdf::loadView('pdf.responsable', compact('data'));

        $nombreLimpio = str_replace(' ', '_', $data['responsable']->nombre_completo);
        $filename = 'Inventario_'.$nombreLimpio.'_'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate a signing link (draft) for inventory by location.
     * The email is sent only after signature is captured.
     */
    public function enviarUbicacion(int $ubicacionId)
    {
        $ubicacion = Ubicacion::with('responsable')->find($ubicacionId);

        if (! $ubicacion) {
            return response()->json(['success' => false, 'message' => 'Ubicación no encontrada'], 404);
        }

        try {
            $envio = $this->firmaEnvioService->crearEnvioBorradorPorUbicacion($ubicacion);

            return response()->json([
                'success' => true,
                'message' => 'Enlace de firma generado. Capture la firma en tablet/celular y el correo se enviará automáticamente.',
                'url_firma' => $this->firmaEnvioService->buildApprovalUrl($envio->token),
                'url_cita_ajuste' => $this->firmaEnvioService->buildCitaAjusteUrl($envio->token),
                'ruta_firma' => '/inventario/aprobar/'.$envio->token,
                'token' => $envio->token,
                'codigo_envio' => $envio->codigo_envio,
                'email_destino' => $envio->email_enviado_a,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error enviando reporte por ubicacion', [
                'ubicacion_id' => $ubicacionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar enlace de firma: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a signing link (draft) for inventory by responsible person.
     * The email is sent only after signature is captured.
     */
    public function enviarResponsable(int $responsableId)
    {
        $responsable = Responsable::find($responsableId);

        if (! $responsable) {
            return response()->json(['success' => false, 'message' => 'Responsable no encontrado'], 404);
        }

        try {
            $envio = $this->firmaEnvioService->crearEnvioBorradorPorResponsable($responsable);

            return response()->json([
                'success' => true,
                'message' => 'Enlace de firma generado. Capture la firma en tablet/celular y el correo se enviará automáticamente.',
                'url_firma' => $this->firmaEnvioService->buildApprovalUrl($envio->token),
                'url_cita_ajuste' => $this->firmaEnvioService->buildCitaAjusteUrl($envio->token),
                'ruta_firma' => '/inventario/aprobar/'.$envio->token,
                'token' => $envio->token,
                'codigo_envio' => $envio->codigo_envio,
                'email_destino' => $envio->email_enviado_a,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error enviando reporte por responsable', [
                'responsable_id' => $responsableId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar enlace de firma: '.$e->getMessage(),
            ], 500);
        }
    }
}
