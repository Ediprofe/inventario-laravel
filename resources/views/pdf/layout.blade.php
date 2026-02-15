<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Inventario')</title>
    @php
        $candaraFontPath = public_path('fonts/Candara.ttf');
    @endphp
    <style>
        @if(is_file($candaraFontPath))
        @font-face {
            font-family: 'CandaraCustom';
            font-style: normal;
            font-weight: 400;
            src: url('file://{{ $candaraFontPath }}') format('truetype');
        }
        @font-face {
            font-family: 'CandaraCustom';
            font-style: italic;
            font-weight: 400;
            src: url('file://{{ $candaraFontPath }}') format('truetype');
        }
        @endif
        @page {
            margin: 1.2cm 1.35cm 1.4cm 1.35cm;
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
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 6px;
            margin-bottom: 14px;
        }
        .membrete-full {
            width: 100%;
            max-height: 110px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        .membrete-table {
            width: 100%;
            border: none;
            margin-bottom: 0;
            table-layout: fixed;
        }
        .membrete-table td {
            border: none !important;
            background: transparent !important;
            vertical-align: middle;
            padding: 0;
        }
        .membrete-escudo {
            width: 16%;
            text-align: left;
            padding-right: 6px !important;
            padding-left: 4px !important;
        }
        .membrete-escudo img {
            width: 84px;
            height: auto;
            display: block;
        }
        .membrete-texto {
            width: 84%;
            text-align: center;
            padding-right: 2px !important;
        }
        .membrete-title {
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            font-size: 23pt;
            font-style: italic;
            font-weight: 700;
            letter-spacing: 0;
            color: #111111;
            line-height: 1.02;
        }
        .membrete-line {
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            font-size: 14.4pt;
            font-style: italic;
            color: #111111;
            line-height: 1.06;
            margin-top: 1px;
        }
        .membrete-id {
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            font-size: 14.4pt;
            font-style: italic;
            color: #111111;
            line-height: 1.06;
            margin-top: 1px;
        }
        .membrete-lema {
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            font-size: 16.3pt;
            font-style: italic;
            font-weight: 700;
            color: #111111;
            line-height: 1.06;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .header-fallback h1 {
            font-size: 20pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
            color: #0f172a;
            text-align: center;
        }
        .header-fallback .subtitle {
            font-size: 10pt;
            color: #64748b;
            font-weight: normal;
            text-align: center;
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
        .summary-panel {
            background: #f8fafc;
            border: 1px solid #dbe5f2;
            border-left: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 10px 12px 11px;
            margin-bottom: 14px;
        }
        .summary-panel table {
            border: none;
            margin-bottom: 0;
            table-layout: auto;
        }
        .summary-panel td {
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
            font-size: 9.6pt;
            vertical-align: top;
        }
        .summary-panel tr:nth-child(even) {
            background: transparent !important;
        }
        .summary-head-table {
            width: 100%;
            margin-bottom: 5px !important;
        }
        .summary-title {
            font-size: 16pt !important;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }
        .summary-generated {
            width: 220px;
            text-align: right;
            font-size: 8pt !important;
            color: #64748b;
            line-height: 1.25;
        }
        .summary-divider {
            border-top: 1px solid #dbe2ec;
            margin: 5px 0 7px;
        }
        .summary-info-table {
            width: 100%;
            margin-bottom: 6px !important;
        }
        .summary-info-table td {
            padding: 1px 8px 1px 0 !important;
        }
        .summary-label {
            color: #334155;
            font-weight: 700;
        }
        .summary-value {
            color: #0f172a;
            font-weight: 500;
        }
        .summary-kpi-table {
            width: 100%;
        }
        .summary-kpi-table td {
            width: 50%;
            padding-right: 8px !important;
        }
        .summary-kpi {
            border: 1px solid #cedcf0;
            background: #ffffff;
            border-radius: 6px;
            padding: 5px 8px 6px;
        }
        .summary-kpi-label {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-kpi-value {
            font-size: 15pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.05;
            margin-top: 2px;
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
        .signature-image {
            height: 58px;
            margin-bottom: 8px;
            text-align: center;
        }
        .signature-image img {
            max-height: 58px;
            max-width: 90%;
        }
        .signature-label {
            font-size: 8.5pt;
            font-weight: bold;
            color: #475569;
        }
        .signature-name {
            font-size: 8pt;
            color: #0f172a;
            margin-top: 2px;
        }
        .signature-role {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
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
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @php
        $membretePath = trim((string) config('institucion.membrete', ''));
        $membreteAbsolute = $membretePath !== '' ? public_path(ltrim($membretePath, '/')) : null;
        if (!$membreteAbsolute || !is_file($membreteAbsolute)) {
            $membreteCandidates = [
                'img/membrete.png',
                'img/membrete.jpg',
                'img/membrete.jpeg',
                'img/membrete-institucional.jpg',
                'img/membrete-institucional.jpeg',
                'img/membrete.PNG',
                'img/membrete.JPG',
                'img/membrete.JPEG',
            ];
            $membreteAbsolute = null;
            foreach ($membreteCandidates as $candidate) {
                $candidateAbsolute = public_path($candidate);
                if (is_file($candidateAbsolute)) {
                    $membreteAbsolute = $candidateAbsolute;
                    break;
                }
            }
        }

        $escudoPath = trim((string) config('institucion.escudo', 'img/escudo.png'));
        $escudoAbsolute = public_path(ltrim($escudoPath, '/'));
        if (!is_file($escudoAbsolute)) {
            $escudoCandidates = ['img/escudo.png', 'img/escudo.jpg', 'img/escudo.jpeg', 'img/escudo.PNG', 'img/escudo.JPG', 'img/escudo.JPEG'];
            $escudoAbsolute = null;
            foreach ($escudoCandidates as $candidate) {
                $candidateAbsolute = public_path($candidate);
                if (is_file($candidateAbsolute)) {
                    $escudoAbsolute = $candidateAbsolute;
                    break;
                }
            }
        }
    @endphp

    <div class="header">
        @if($membreteAbsolute && is_file($membreteAbsolute))
            <img class="membrete-full" src="file://{{ $membreteAbsolute }}" alt="Membrete institucional">
        @elseif($escudoAbsolute && is_file($escudoAbsolute))
            <table class="membrete-table">
                <tr>
                    <td class="membrete-escudo">
                        <img src="file://{{ $escudoAbsolute }}" alt="Escudo institucional">
                    </td>
                    <td class="membrete-texto">
                        <div class="membrete-title">{{ config('institucion.nombre_largo', config('institucion.nombre')) }}</div>
                        <div class="membrete-line">{{ config('institucion.resolucion_texto') }}</div>
                        <div class="membrete-id">{{ config('institucion.identificacion_texto') }}</div>
                        <div class="membrete-lema">"{{ config('institucion.lema') }}"</div>
                    </td>
                </tr>
            </table>
        @else
            <div class="header-fallback">
                <h1>{{ config('institucion.nombre') }}</h1>
                @if(config('institucion.nit'))
                    <div class="subtitle">NIT: {{ config('institucion.nit') }}</div>
                @endif
                @if(config('institucion.direccion'))
                    <div class="subtitle">{{ config('institucion.direccion') }} - {{ config('institucion.ciudad') }}</div>
                @endif
            </div>
        @endif
    </div>

    @yield('content')

    <div class="footer">
        <div class="signature-container">
            <table class="signature-table">
                <tr>
                    <td>
                        @if(isset($envio) && $envio->firma_base64)
                            <div class="signature-image">
                                <img src="{{ $envio->firma_base64 }}" alt="Firma responsable">
                            </div>
                        @endif
                        <div class="signature-line"></div>
                        <div class="signature-label">Firma del Responsable</div>
                        <div class="signature-name">{{ isset($envio) ? ($envio->firmante_nombre ?: ($envio->responsable?->nombre_completo ?? '')) : '' }}</div>
                    </td>
                    <td>
                        @if(isset($firmaEntrega) && !empty($firmaEntrega['base64']))
                            <div class="signature-image">
                                <img src="{{ $firmaEntrega['base64'] }}" alt="Firma entrega/verifica">
                            </div>
                        @endif
                        <div class="signature-line"></div>
                        <div class="signature-label">Firma de quien entrega / verifica</div>
                        @if(isset($firmaEntrega) && !empty($firmaEntrega['nombre']))
                            <div class="signature-name">{{ $firmaEntrega['nombre'] }}</div>
                            @if(!empty($firmaEntrega['cargo']))
                                <div class="signature-role">{{ $firmaEntrega['cargo'] }}</div>
                            @endif
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
