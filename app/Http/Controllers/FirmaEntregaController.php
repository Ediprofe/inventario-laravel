<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventario\GuardarFirmaEntregaRequest;
use App\Models\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FirmaEntregaController extends Controller
{
    public function mostrar(Request $request, Responsable $responsable)
    {
        abort_unless($request->hasValidSignature(false), 403);

        return view('inventario.firma-entrega', [
            'responsable' => $responsable,
            'signedPostUrl' => $request->fullUrl(),
        ]);
    }

    public function guardar(GuardarFirmaEntregaRequest $request, Responsable $responsable)
    {
        abort_unless($request->hasValidSignature(false), 403);

        $data = $request->validated()['firma_data'];
        $encoded = substr($data, strpos($data, ',') + 1);
        $binary = base64_decode($encoded, true);

        if ($binary === false) {
            return back()->withErrors([
                'firma_data' => 'La firma no es válida, por favor intente de nuevo.',
            ])->withInput();
        }

        $path = 'firmas-entrega/'.Str::uuid().'.png';
        Storage::disk('public')->put($path, $binary);

        if ($responsable->firma_entrega_path && Storage::disk('public')->exists($responsable->firma_entrega_path)) {
            Storage::disk('public')->delete($responsable->firma_entrega_path);
        }

        $responsable->update([
            'firma_entrega_path' => $path,
            'es_firmante_entrega' => true,
        ]);

        return back()->with('success', 'Firma de entrega guardada correctamente. Ya puede cerrar esta ventana.');
    }
}
