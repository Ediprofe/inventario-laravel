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
        }
        .header {
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #1e40af;
            font-size: 20px;
            margin: 0;
        }
        .content {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>📦 Sistema de Inventario</h1>
    </div>
    
    <p>Estimado(a) <strong>{{ $destinatario }}</strong>,</p>
    
    <div class="content">
        <p>Se adjunta el reporte de inventario solicitado:</p>
        <p>
            <span class="badge">{{ $tipoReporte }}</span>
            <strong>{{ $nombreReporte }}</strong>
        </p>
    </div>
    
    <p>Por favor revise el documento adjunto y comunique cualquier novedad.</p>
    
    <div class="footer">
        <p>
            <strong>{{ config('institucion.nombre', 'Institución Educativa') }}</strong><br>
            Este es un mensaje automático del Sistema de Inventario.
        </p>
    </div>
</body>
</html>
