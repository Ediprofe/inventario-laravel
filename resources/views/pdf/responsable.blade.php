@extends('pdf.layout')

@section('title', 'Inventario - ' . $data['responsable']->nombre_completo)

@section('content')
    <div class="info-card">
        <h2>INVENTARIO POR RESPONSABLE</h2>
        <div class="info-row">
            <span class="info-label">Responsable:</span>
            <span class="info-value">{{ $data['responsable']->nombre_completo }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cargo:</span>
            <span class="info-value">{{ $data['responsable']->cargo ?? 'Sin especificar' }}</span>
        </div>
        @if($data['responsable']->email)
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $data['responsable']->email }}</span>
        </div>
        @endif
        <div class="info-row">
            <span class="info-label">Total Items:</span>
            <span class="info-value" style="font-size: 11pt; font-weight: bold;">{{ $data['total'] }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Items En Uso:</span>
            <span class="info-value" style="font-size: 10pt; font-weight: bold;">{{ $data['total_en_uso'] ?? 0 }}</span>
        </div>
    </div>

    {{-- Resumen de Inventario --}}
    <h3 class="section-title">RESUMEN DE INVENTARIO</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 13%">Cód. Ubicación</th>
                <th style="width: 22%">Ubicación</th>
                <th style="width: 25%">Artículo</th>
                <th style="width: 8%; text-align: center">Cant.</th>
                <th style="width: 16%">Disponibilidad</th>
                <th style="width: 16%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $item)
                <tr>
                    <td style="font-family: monospace; font-size: 8pt;">{{ $item['ubicacion_codigo'] }}</td>
                    <td>{{ $item['ubicacion_nombre'] }}</td>
                    <td style="font-weight: bold;">{{ $item['articulo'] }}</td>
                    <td style="text-align: center; border-left: 1px solid #e2e8f0;">{{ $item['cantidad'] }}</td>
                    <td>
                        @foreach($item['disponibilidades'] as $disponibilidad)
                            <div style="font-size: 7.5pt; color: #475569;">
                                {{ $disponibilidad['label'] }}: <strong>{{ $disponibilidad['count'] }}</strong>
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($item['estados'] as $estado)
                            <div style="font-size: 7.5pt; color: #475569;">
                                {{ $estado['label'] }}: <strong>{{ $estado['count'] }}</strong>
                            </div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" style="text-align: right; text-transform: uppercase; font-size: 8pt; letter-spacing: 1px;">Suma Total de Items</td>
                <td style="text-align: center; font-size: 11pt;">{{ $data['total'] }}</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
@endsection
