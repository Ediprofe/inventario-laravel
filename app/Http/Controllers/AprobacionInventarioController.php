<?php

namespace App\Http\Controllers;

use App\Models\EnvioInventario;
use Illuminate\Http\Request;

class AprobacionInventarioController extends Controller
{
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
    public function confirmar(Request $request, string $token)
    {
        $envio = EnvioInventario::where('token', $token)->firstOrFail();

        if ($envio->estaAprobado()) {
            return redirect()->back()->with('info', 'Este inventario ya fue aprobado anteriormente.');
        }

        $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $envio->update([
            'aprobado_at' => now(),
            'ip_aprobacion' => $request->ip(),
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->back()->with('success', 'Inventario aprobado exitosamente.');
    }
}
