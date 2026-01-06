<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Inventario')</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            /* Ensure content doesn't hit the absolute edge */
            padding: 0 10px; 
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 12px;
            color: #666;
        }
        .info-box {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .info-box h2 {
            font-size: 14px;
            margin-bottom: 8px;
            color: #333;
        }
        .info-row {
            margin-bottom: 4px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #4a5568;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        .signature-container {
            width: 100%;
            margin-top: 60px;
        }
        .signature-table {
            width: 100%;
            border: none;
        }
        .signature-table td {
            border: none;
            width: 50%;
            text-align: center;
            padding: 0 20px;
            vertical-align: top;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-bottom: 5px;
            width: 100%;
        }
        .signature-label {
            font-size: 10px;
            color: #333;
        }
        .total-row {
            font-weight: bold;
            background-color: #e2e8f0 !important;
        }
        .estado-breakdown {
            font-size: 10px;
            color: #555;
        }
        .fecha-generacion {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('institucion.nombre') }}</h1>
        @if(config('institucion.nit'))
            <div class="subtitle">NIT: {{ config('institucion.nit') }}</div>
        @endif
        @if(config('institucion.direccion'))
            <div class="subtitle">{{ config('institucion.direccion') }} - {{ config('institucion.ciudad') }}</div>
        @endif
    </div>

    <div class="fecha-generacion">
        Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

    @yield('content')

    <div class="footer">
        <div class="signature-container">
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-label">Firma del Responsable</div>
                    </td>
                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-label">Firma de quien entrega / verifica</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
