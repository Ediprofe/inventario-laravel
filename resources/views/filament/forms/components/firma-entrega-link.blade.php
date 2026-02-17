@php
    use Illuminate\Support\Facades\URL;

    $record = $getRecord();
    $urlFirma = null;
    $domId = $record?->id ? ('firma-entrega-' . $record->id) : 'firma-entrega-new';

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

            <div style="margin-top:12px; display:flex; gap:14px; flex-wrap:wrap; align-items:flex-start;">
                <div style="min-width:220px; flex:0 0 220px; border:1px solid #334155; border-radius:10px; background:#020617; padding:10px; text-align:center;">
                    <canvas id="{{ $domId }}-qr" width="220" height="220" style="display:block; width:220px; height:220px; margin:0 auto; background:#fff; border-radius:8px;"></canvas>
                    <img id="{{ $domId }}-qr-image" alt="QR firma entrega" style="display:none; width:220px; height:220px; margin:0 auto; background:#fff; border-radius:8px;" />
                    <div id="{{ $domId }}-qr-status" style="margin-top:8px; font-size:11px; color:#94a3b8;">Escanea este QR desde la camara de la tablet.</div>
                </div>

                <div style="flex:1 1 420px; min-width:280px;">
                    <textarea
                        id="{{ $domId }}-link"
                        readonly
                        rows="3"
                        style="width:100%; border:1px solid #475569; border-radius:10px; background:#020617; color:#e2e8f0; padding:10px; font-size:12px; line-height:1.45; resize:vertical;"
                    >{{ $urlFirma }}</textarea>

                    <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                        <button
                            type="button"
                            style="border:none; border-radius:8px; padding:8px 12px; background:#f59e0b; color:#111827; font-weight:600; cursor:pointer;"
                            onclick="(async function(){ const input=document.getElementById('{{ $domId }}-link'); const status=document.getElementById('{{ $domId }}-copy-status'); const value=input?.value||''; try { await navigator.clipboard.writeText(value); if(status){ status.textContent='Enlace copiado.'; setTimeout(function(){ status.textContent=''; }, 2500); } } catch (e) { prompt('Copie este enlace:', value); } })()"
                        >Copiar enlace</button>

                        <a
                            href="{{ $urlFirma }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            style="display:inline-block; border-radius:8px; padding:8px 12px; background:#1d4ed8; color:#f8fafc; font-weight:600; text-decoration:none;"
                        >Abrir enlace</a>

                        <span id="{{ $domId }}-copy-status" style="font-size:12px; color:#93c5fd;"></span>
                    </div>
                </div>
            </div>

            <script>
                (function () {
                    const domId = @json($domId);
                    const link = @json($urlFirma);
                    const canvas = document.getElementById(domId + '-qr');
                    const image = document.getElementById(domId + '-qr-image');
                    const status = document.getElementById(domId + '-qr-status');

                    if (!canvas || !image || !link) {
                        return;
                    }

                    const showImageFallback = function () {
                        image.src = 'https://quickchart.io/qr?size=220&margin=1&text=' + encodeURIComponent(link);
                        image.style.display = 'block';
                        canvas.style.display = 'none';
                        if (status) {
                            status.textContent = 'QR generado por fallback. Escanee desde la tablet.';
                        }
                    };

                    const renderQr = function () {
                        if (!window.QRCode || typeof window.QRCode.toCanvas !== 'function') {
                            showImageFallback();
                            return;
                        }

                        window.QRCode.toCanvas(canvas, link, {
                            width: 220,
                            margin: 1,
                            color: {
                                dark: '#0f172a',
                                light: '#ffffff',
                            },
                        }, function (error) {
                            if (!status) {
                                return;
                            }
                            if (error) {
                                showImageFallback();
                                return;
                            }

                            image.style.display = 'none';
                            canvas.style.display = 'block';
                            status.textContent = 'Escanea este QR desde la camara de la tablet.';
                        });
                    };

                    if (window.QRCode) {
                        renderQr();
                        return;
                    }

                    const existing = document.getElementById('codex-qrcode-lib');
                    if (existing) {
                        const waitForLib = setInterval(function () {
                            if (window.QRCode) {
                                clearInterval(waitForLib);
                                renderQr();
                            }
                        }, 100);

                        setTimeout(function () {
                            clearInterval(waitForLib);
                        }, 6000);
                        return;
                    }

                    const script = document.createElement('script');
                    script.id = 'codex-qrcode-lib';
                    script.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js';
                    script.async = true;
                    script.onload = renderQr;
                    script.onerror = function () {
                        showImageFallback();
                    };
                    document.head.appendChild(script);
                })();
            </script>
        </div>
    @endif
</x-dynamic-component>
