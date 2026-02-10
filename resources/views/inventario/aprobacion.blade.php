<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobación de Inventario</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            max-width: 560px;
            width: 100%;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            padding: 28px 32px;
            text-align: center;
        }
        .card-header h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .card-header p {
            font-size: 13px;
            opacity: 0.85;
        }
        .card-body {
            padding: 32px;
        }
        .info-grid {
            display: grid;
            gap: 12px;
            margin-bottom: 24px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .info-item .label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }
        .info-item .value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-approved {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-type {
            background: #dbeafe;
            color: #1e40af;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            transition: border-color 0.2s;
        }
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-approve {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
        }
        .btn-approve:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .approved-box {
            text-align: center;
            padding: 24px;
            background: #f0fdf4;
            border-radius: 12px;
            border: 1px solid #bbf7d0;
        }
        .approved-box .icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        .approved-box h3 {
            color: #065f46;
            font-size: 18px;
            margin-bottom: 8px;
        }
        .approved-box p {
            color: #047857;
            font-size: 13px;
        }
        .footer {
            text-align: center;
            padding: 16px 32px 24px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h1>📦 {{ config('institucion.nombre', 'Institución Educativa') }}</h1>
            <p>Verificación de Inventario</p>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">✅ {{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">ℹ️ {{ session('info') }}</div>
            @endif

            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Responsable</span>
                    <span class="value">{{ $envio->responsable->nombre_completo }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Tipo</span>
                    <span class="badge badge-type">
                        {{ $envio->tipo === 'por_ubicacion' ? '📍 Por Ubicación' : '👤 Por Responsable' }}
                    </span>
                </div>
                @if($envio->ubicacion)
                <div class="info-item">
                    <span class="label">Ubicación</span>
                    <span class="value">{{ $envio->ubicacion->nombre }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="label">Fecha de envío</span>
                    <span class="value">{{ $envio->enviado_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Estado</span>
                    @if($envio->estaAprobado())
                        <span class="badge badge-approved">✅ Aprobado</span>
                    @else
                        <span class="badge badge-pending">⏳ Pendiente</span>
                    @endif
                </div>
            </div>

            @if($envio->estaAprobado())
                <div class="approved-box">
                    <div class="icon">✅</div>
                    <h3>Inventario Aprobado</h3>
                    <p>Aprobado el {{ $envio->aprobado_at->format('d/m/Y') }} a las {{ $envio->aprobado_at->format('H:i') }}</p>
                    @if($envio->observaciones)
                        <p style="margin-top: 12px; font-style: italic;">"{{ $envio->observaciones }}"</p>
                    @endif
                </div>
            @else
                <form action="{{ url('/inventario/aprobar/' . $envio->token) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="observaciones">Observaciones (opcional)</label>
                        <textarea name="observaciones" id="observaciones" 
                                  placeholder="Si tiene alguna novedad o comentario sobre su inventario, escríbalo aquí..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-approve">
                        ✅ Aprobar Inventario
                    </button>
                </form>
            @endif
        </div>

        <div class="footer">
            {{ config('institucion.nombre', 'Institución Educativa') }} — Sistema de Inventario
        </div>
    </div>
</body>
</html>
