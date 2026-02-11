@extends('pdf.layout')

@section('title', 'Inventario - ' . $data['ubicacion']->codigo . ' - ' . $data['ubicacion']->nombre)

@section('content')
    <div class="info-card">
        <h2>INVENTARIO - {{ $data['ubicacion']->codigo }} - {{ $data['ubicacion']->nombre }}</h2>
        <div class="info-row">
            <span class="info-label">Ubicación:</span>
            <span class="info-value">{{ $data['ubicacion']->nombre }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Código:</span>
            <span class="info-value">{{ $data['ubicacion']->codigo }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Sede:</span>
            <span class="info-value">{{ $data['ubicacion']->sede->nombre }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Responsable:</span>
            <span class="info-value">{{ $data['ubicacion']->responsable?->nombre_completo ?? 'Sin asignar' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Items:</span>
            <span class="info-value" style="font-size: 11pt; font-weight: bold;">{{ $data['total'] }}</span>
        </div>
    </div>

    <h3 class="section-title">RESUMEN DE ARTÍCULOS</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 50%">Artículo</th>
                <th style="width: 15%; text-align: center">Cantidad</th>
                <th style="width: 35%">Desglose por Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $item)
                <tr>
                    <td style="font-weight: bold;">{{ $item['articulo'] }}</td>
                    <td style="text-align: center; font-size: 11pt;">{{ $item['cantidad'] }}</td>
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
