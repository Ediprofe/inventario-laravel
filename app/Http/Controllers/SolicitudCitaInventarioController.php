<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventario\SolicitarCitaAjusteRequest;
use App\Models\EnvioInventario;
use App\Models\SolicitudAjusteInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SolicitudCitaInventarioController extends Controller
{
    public function mostrar(string $token): View
    {
        $envio = $this->getEnvioByToken($token);

        return view('inventario.solicitud-cita-ajuste', [
            'envio' => $envio,
            'responsable' => $envio->responsable,
            'ubicacion' => $envio->ubicacion,
        ]);
    }

    public function guardar(SolicitarCitaAjusteRequest $request, string $token): RedirectResponse
    {
        $envio = $this->getEnvioByToken($token);
        $validated = $request->validated();
        $contactoDetalle = $this->resolveContactoDetalle($envio, $validated['medio_contacto'], $validated['whatsapp_manual'] ?? null);

        $solicitud = SolicitudAjusteInventario::query()->create([
            'envio_inventario_id' => $envio->id,
            'responsable_id' => $envio->responsable_id,
            'ubicacion_id' => $envio->ubicacion_id,
            'tipo_solicitud' => $validated['tipo_solicitud'],
            'estado' => 'pendiente',
            'solicitante_nombre' => $validated['solicitante_nombre'],
            'medio_contacto' => $validated['medio_contacto'],
            'contacto_detalle' => $contactoDetalle,
            'franja_horaria' => $validated['franja_horaria'] !== '' ? $validated['franja_horaria'] : null,
            'detalle' => $validated['detalle'],
            'confirmado_coordinacion' => true,
            'solicitado_at' => now(),
        ]);

        return redirect()
            ->route('inventario.cita-ajuste.mostrar', ['token' => $token])
            ->with('success', "Solicitud registrada correctamente ({$solicitud->codigo_solicitud}).");
    }

    private function getEnvioByToken(string $token): EnvioInventario
    {
        return EnvioInventario::query()
            ->with(['responsable', 'ubicacion'])
            ->where('token', $token)
            ->firstOrFail();
    }

    private function resolveContactoDetalle(EnvioInventario $envio, string $medioContacto, ?string $whatsappManual): ?string
    {
        if ($medioContacto === 'whatsapp') {
            $telefono = trim((string) ($envio->responsable?->telefono ?? ''));
            if ($telefono !== '') {
                return $telefono;
            }

            $manual = trim((string) $whatsappManual);
            if ($manual === '') {
                throw ValidationException::withMessages([
                    'whatsapp_manual' => 'No encontramos WhatsApp registrado. Escríbalo para poder contactarle.',
                ]);
            }

            return $manual;
        }

        $correo = trim((string) ($envio->responsable?->email ?? ''));
        if ($correo === '') {
            throw ValidationException::withMessages([
                'medio_contacto' => 'No encontramos correo registrado. Seleccione WhatsApp.',
            ]);
        }

        return $correo;
    }
}
