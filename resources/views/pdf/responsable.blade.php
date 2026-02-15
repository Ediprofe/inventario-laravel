@extends('pdf.layout')

@section('title', 'Inventario - ' . $data['responsable']->nombre_completo)

@section('content')
    <div class="summary-panel">
        <table class="summary-head-table">
            <tr>
                <td class="summary-title">Inventario Por Responsable</td>
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
                <td><span class="summary-label">Responsable:</span> <span class="summary-value">{{ $data['responsable']->nombre_completo }}</span></td>
                <td><span class="summary-label">Cargo:</span> <span class="summary-value">{{ $data['responsable']->cargo ?? 'Sin especificar' }}</span></td>
            </tr>
            @if($data['responsable']->email)
                <tr>
                    <td colspan="2"><span class="summary-label">Email:</span> <span class="summary-value">{{ $data['responsable']->email }}</span></td>
                </tr>
            @endif
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
