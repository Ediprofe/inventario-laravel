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
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #1e293b;
            padding: 0; 
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #1e293b;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 20pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
            color: #0f172a;
        }
        .header .subtitle {
            font-size: 10pt;
            color: #64748b;
            font-weight: normal;
        }
        .info-card {
            background-color: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 0 8px 8px 0;
        }
        .info-card h2 {
            font-size: 13pt;
            margin-bottom: 10px;
            color: #1e293b;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 4px;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            width: 140px;
            display: inline-block;
        }
        .info-value {
            color: #1e293b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #1e293b;
        }
        td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            word-wrap: break-word;
            font-size: 9pt;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
            margin: 25px 0 12px 0;
            padding-left: 10px;
            border-left: 4px solid #1e293b;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        .signature-container {
            width: 100%;
            margin-top: 50px;
        }
        .signature-table {
            width: 100%;
            border: none;
        }
        .signature-table td {
            border: none !important;
            width: 50%;
            text-align: center;
            padding: 0 25px;
            vertical-align: top;
            background: none !important;
        }
        .signature-line {
            border-top: 1px solid #1e293b;
            margin-bottom: 5px;
            width: 100%;
        }
        .signature-label {
            font-size: 8.5pt;
            font-weight: bold;
            color: #475569;
        }
        .total-row {
            font-weight: bold;
            background-color: #f1f5f9 !important;
            color: #0f172a;
        }
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-bueno { background-color: #dcfce7; color: #166534; }
        .badge-regular { background-color: #fef9c3; color: #854d0e; }
        .badge-malo { background-color: #fee2e2; color: #991b1b; }
        
        .fecha-generacion {
            text-align: right;
            font-size: 8.5pt;
            color: #64748b;
            margin-bottom: 15px;
            font-style: italic;
        }
        .page-break {
            page-break-before: always;
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
