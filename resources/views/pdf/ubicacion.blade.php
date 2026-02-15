@extends('pdf.layout')

@section('title', 'Inventario - ' . $data['ubicacion']->codigo . ' - ' . $data['ubicacion']->nombre)

@section('content')
    <div class="summary-panel">
        <table class="summary-head-table">
            <tr>
                <td class="summary-title">Inventario - {{ $data['ubicacion']->codigo }} - {{ $data['ubicacion']->nombre }}</td>
                <td class="summary-generated">
                    Generado: {{ now()->format('d/m/Y H:i') }}
                    @if(isset($envio))
                        <br>Envío: {{ $envio->codigo_envio }}
                        <br>Firma: {{ $envio->aprobado_at?->format('d/m/Y H:i') ?? 'Pendiente' }}
                    @endif
                </td>
            </tr>
        </table>

        <div class="summary-divider"></div>

        <table class="summary-info-table">
            <tr>
                <td><span class="summary-label">Ubicación:</span> <span class="summary-value">{{ $data['ubicacion']->nombre }}</span></td>
                <td><span class="summary-label">Código:</span> <span class="summary-value">{{ $data['ubicacion']->codigo }}</span></td>
                <td><span class="summary-label">Sede:</span> <span class="summary-value">{{ $data['ubicacion']->sede->nombre }}</span></td>
            </tr>
            <tr>
                <td colspan="3"><span class="summary-label">Responsable:</span> <span class="summary-value">{{ $data['ubicacion']->responsable?->nombre_completo ?? 'Sin asignar' }}</span></td>
            </tr>
        </table>

        <table class="summary-kpi-table">
            <tr>
                <td>
                    <div class="summary-kpi">
                        <div class="summary-kpi-label">Total Items</div>
                        <div class="summary-kpi-value">{{ $data['total'] }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-kpi">
                        <div class="summary-kpi-label">Items En Uso</div>
                        <div class="summary-kpi-value">{{ $data['total_en_uso'] ?? 0 }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <h3 class="section-title">RESUMEN DE ARTÍCULOS</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 35%">Artículo</th>
                <th style="width: 10%; text-align: center">Cantidad</th>
                <th style="width: 27%">Disponibilidad</th>
                <th style="width: 28%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $item)
                <tr>
                    <td style="font-weight: bold;">{{ $item['articulo'] }}</td>
                    <td style="text-align: center; font-size: 11pt;">{{ $item['cantidad'] }}</td>
                    <td>
                        @foreach($item['disponibilidades'] as $disponibilidad)
                            <div style="font-size: 8pt; color: #475569;">
                                {{ $disponibilidad['label'] }}: <strong>{{ $disponibilidad['count'] }}</strong>
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($item['estados'] as $estado)
                            <div style="font-size: 8pt; color: #475569;">
                                {{ $estado['label'] }}: <strong>{{ $estado['count'] }}</strong>
                            </div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td style="text-align: right; text-transform: uppercase; font-size: 8pt; letter-spacing: 1px;">Total de Items</td>
                <td style="text-align: center; font-size: 12pt;">{{ $data['total'] }}</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- Observaciones --}}
    @if($data['ubicacion']->observaciones)
        <div class="info-card" style="border-left-color: #f59e0b; background-color: #fffbeb; margin-top: 20px;">
            <h2 style="font-size: 11pt; color: #92400e; border-bottom-color: #fde68a;">OBSERVACIONES</h2>
            <p style="font-size: 9.5pt; color: #78350f; white-space: pre-line;">{{ $data['ubicacion']->observaciones }}</p>
        </div>
    @endif
@endsection
