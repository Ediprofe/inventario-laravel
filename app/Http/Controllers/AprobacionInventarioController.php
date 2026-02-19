<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventario\ConfirmarInventarioFirmaRequest;
use App\Models\EnvioInventario;
use App\Services\InventarioFirmaEnvioService;
use Illuminate\Support\Facades\Log;

class AprobacionInventarioController extends Controller
{
    public function __construct(
        protected InventarioFirmaEnvioService $firmaEnvioService,
    ) {}

    /**
     * Show the approval page for an inventory submission
     */
    public function mostrar(string $token)
    {
        $envio = EnvioInventario::where('token', $token)
            ->with(['responsable', 'ubicacion'])
            ->firstOrFail();

        return view('inventario.aprobacion', compact('envio'));
    }

    /**
     * Confirm/approve the inventory submission
     */
    public function confirmar(ConfirmarInventarioFirmaRequest $request, string $token)
    {
        $envio = EnvioInventario::where('token', $token)->firstOrFail();

        if ($envio->estaAprobado()) {
            return redirect()->back()->with('info', 'Este inventario ya fue firmado anteriormente.');
        }

        $validated = $request->validated();

        $envio->update([
            'aprobado_at' => now(),
            'ip_aprobacion' => $request->ip(),
            'firmante_nombre' => $validated['firmante_nombre'],
            'firma_base64' => $validated['firma_data'],
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        try {
            $resultado = $this->firmaEnvioService->enviarInventarioFirmado($envio->fresh());

            return redirect()->back()->with(
                'success',
                "Inventario firmado y enviado a {$resultado['email']} (envío {$resultado['codigo_envio']})."
            );
        } catch (\Throwable $e) {
            Log::error('Firma guardada pero fallo el envio de correo', [
                'envio_id' => $envio->id,
                'token' => $envio->token,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with(
                'info',
                'La firma quedó registrada, pero el correo no se pudo enviar. Puede reintentar desde soporte técnico.'
            );
        }
    }
}
