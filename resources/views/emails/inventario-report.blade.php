<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            padding: 24px 32px;
            text-align: center;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 4px 0;
        }
        .header p {
            font-size: 12px;
            opacity: 0.85;
            margin: 0;
        }
        .body-content {
            padding: 32px;
        }
        .content {
            background-color: #f0f9ff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #bae6fd;
        }
        .badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .meta {
            font-size: 12px;
            color: #475569;
            margin-top: 14px;
            line-height: 1.7;
        }
        .signatures {
            margin-top: 18px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .signature-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
            background: #f8fafc;
        }
        .signature-card h4 {
            margin: 0 0 6px 0;
            font-size: 12px;
            color: #1e293b;
        }
        .signature-card p {
            margin: 0 0 6px 0;
            font-size: 11px;
            color: #475569;
        }
        .signature-card img {
            width: 100%;
            max-height: 100px;
            object-fit: contain;
            background: white;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 4px;
        }
        .footer {
            padding: 20px 32px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 {{ config('institucion.nombre', 'Institución Educativa') }}</h1>
            <p>Sistema de Inventario</p>
        </div>
        
        <div class="body-content">
            @php
                $embedSignature = function (?string $dataUri, string $filename) use ($message) {
                    if (!isset($message) || !$dataUri || !str_contains($dataUri, ',')) {
                        return null;
                    }

                    [$meta, $encoded] = explode(',', $dataUri, 2);
                    if (!str_contains($meta, ';base64')) {
                        return null;
                    }

                    $mime = 'image/png';
                    if (preg_match('/^data:(.*?);base64$/', $meta, $matches) === 1 && !empty($matches[1])) {
                        $mime = $matches[1];
                    }

                    $binary = base64_decode($encoded, true);
                    if ($binary === false) {
                        return null;
                    }

                    try {
                        return $message->embedData($binary, $filename, $mime);
                    } catch (\Throwable) {
                        return null;
                    }
                };

                $firmaResponsableCid = $embedSignature($firmaResponsableBase64 ?? null, 'firma_responsable.png');
                $firmaEntregaCid = $embedSignature($firmaEntregaBase64 ?? null, 'firma_entrega.png');
            @endphp

            <p>Estimado(a) <strong>{{ $destinatario }}</strong>,</p>
            
            <div class="content">
                <p style="margin: 0 0 8px 0;">Se adjunta el reporte de inventario:</p>
                <p style="margin: 0;">
                    <span class="badge">{{ $tipoReporte }}</span>
                    <strong>{{ $nombreReporte }}</strong>
                </p>
            </div>

            <p>Adjunto encontrará el PDF y el Excel de este inventario, ya firmados y listos para archivo.</p>

            <div class="meta">
                <div><strong>Código de envío:</strong> {{ $codigoEnvio ?? 'N/A' }}</div>
                <div><strong>Firma responsable:</strong> {{ $firmanteNombre ?? 'No registrada' }}</div>
                @if($firmaEntregaNombre)
                    <div>
                        <strong>Entrega/verifica:</strong> {{ $firmaEntregaNombre }}
                        @if($firmaEntregaCargo)
                            ({{ $firmaEntregaCargo }})
                        @endif
                    </div>
                @endif
                <div><strong>Relación documental:</strong> Este código identifica los archivos PDF y Excel adjuntos.</div>
            </div>

            @if($firmaResponsableBase64 || $firmaEntregaBase64)
                <div class="signatures">
                    <div class="signature-card">
                        <h4>Firma responsable</h4>
                        <p>{{ $firmanteNombre ?? 'Sin nombre' }}</p>
                        @if($firmaResponsableCid)
                            <img src="{{ $firmaResponsableCid }}" alt="Firma responsable">
                        @else
                            <p>Sin imagen de firma.</p>
                        @endif
                    </div>
                    <div class="signature-card">
                        <h4>Firma entrega/verifica</h4>
                        <p>{{ $firmaEntregaNombre ?? 'Sin nombre' }}</p>
                        @if($firmaEntregaCid)
                            <img src="{{ $firmaEntregaCid }}" alt="Firma de entrega">
                        @else
                            <p>Sin imagen de firma.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        
        <div class="footer">
            <strong>{{ config('institucion.nombre', 'Institución Educativa') }}</strong><br>
            Este es un mensaje automático del Sistema de Inventario.
        </div>
    </div>
</body>
</html>
