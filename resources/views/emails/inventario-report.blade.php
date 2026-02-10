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
        .btn-approve {
            display: block;
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            margin: 24px 0 12px;
        }
        .btn-approve:hover {
            opacity: 0.95;
        }
        .approve-note {
            text-align: center;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 20px;
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
            <p>Estimado(a) <strong>{{ $destinatario }}</strong>,</p>
            
            <div class="content">
                <p style="margin: 0 0 8px 0;">Se adjunta el reporte de inventario:</p>
                <p style="margin: 0;">
                    <span class="badge">{{ $tipoReporte }}</span>
                    <strong>{{ $nombreReporte }}</strong>
                </p>
            </div>
            
            <p>Por favor revise el documento adjunto y confirme que la información es correcta.</p>

            @if($urlAprobacion)
                <a href="{{ $urlAprobacion }}" class="btn-approve" target="_blank">
                    ✅ Aprobar Inventario
                </a>
                <p class="approve-note">
                    Al hacer clic, podrá revisar un resumen y confirmar su inventario.
                </p>
            @endif
        </div>
        
        <div class="footer">
            <strong>{{ config('institucion.nombre', 'Institución Educativa') }}</strong><br>
            Este es un mensaje automático del Sistema de Inventario.
        </div>
    </div>
</body>
</html>
