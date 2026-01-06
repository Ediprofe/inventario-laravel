@extends('pdf.layout')

@section('title', 'Inventario - ' . $data['ubicacion']->nombre)

@section('content')
    <div class="info-box">
        <h2>INVENTARIO POR UBICACIÓN</h2>
        <div class="info-row">
            <span class="info-label">Ubicación:</span>
            {{ $data['ubicacion']->nombre }}
        </div>
        <div class="info-row">
            <span class="info-label">Código:</span>
            {{ $data['ubicacion']->codigo }}
        </div>
        <div class="info-row">
            <span class="info-label">Sede:</span>
            {{ $data['ubicacion']->sede->nombre }}
        </div>
        <div class="info-row">
            <span class="info-label">Responsable:</span>
            {{ $data['ubicacion']->responsable?->nombre_completo ?? 'Sin asignar' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40%">Artículo</th>
                <th style="width: 15%">Cantidad</th>
                <th style="width: 45%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $item)
                <tr>
                    <td>{{ $item['articulo'] }}</td>
                    <td style="text-align: center">{{ $item['cantidad'] }}</td>
                    <td class="estado-breakdown">
                        @foreach($item['estados'] as $estado)
                            {{ $estado['label'] }}: {{ $estado['count'] }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td style="text-align: center">{{ $data['total'] }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
@endsection
