@php
    use Illuminate\Support\Facades\URL;

    $record = $getRecord();
    $urlFirma = null;

    if ($record?->id) {
        $signedPath = URL::temporarySignedRoute(
            'firma.entrega.capturar',
            now()->addHours(8),
            ['responsable' => $record->id],
            absolute: false,
        );

        $baseUrl = rtrim((string) config('app.public_url', config('app.url')), '/');
        $urlFirma = $baseUrl . $signedPath;
    }
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @if(!$record?->id)
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Guarde primero el responsable para generar un enlace de firma en tablet/celular.
        </div>
    @else
        <div style="border:1px solid #334155; border-radius:12px; background:#0f172a; padding:14px;">
            <div style="font-size:14px; font-weight:600; color:#f8fafc;">Captura de firma en tablet/celular</div>
            <div style="margin-top:4px; font-size:12px; color:#94a3b8;">Enlace temporal de 8 horas para este responsable.</div>

            <textarea
                id="firma-entrega-link-{{ $record->id }}"
                readonly
                rows="3"
                style="margin-top:10px; width:100%; border:1px solid #475569; border-radius:10px; background:#020617; color:#e2e8f0; padding:10px; font-size:12px; line-height:1.45; resize:vertical;"
            >{{ $urlFirma }}</textarea>

            <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;">
                <button
                    type="button"
                    style="border:none; border-radius:8px; padding:8px 12px; background:#f59e0b; color:#111827; font-weight:600; cursor:pointer;"
                    onclick="(async function(){ const input=document.getElementById('firma-entrega-link-{{ $record->id }}'); const value=input?.value||''; try { await navigator.clipboard.writeText(value); alert('Enlace copiado.'); } catch (e) { prompt('Copie este enlace:', value); } })()"
                >Copiar enlace</button>

                <a
                    href="{{ $urlFirma }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    style="display:inline-block; border-radius:8px; padding:8px 12px; background:#1d4ed8; color:#f8fafc; font-weight:600; text-decoration:none;"
                >Abrir enlace</a>
            </div>
        </div>
    @endif
</x-dynamic-component>
