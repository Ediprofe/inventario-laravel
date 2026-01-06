@extends('pdf.layout')

@section('title', 'Inventario - ' . $data['responsable']->nombre_completo)

@section('content')
    <div class="info-box">
        <h2>INVENTARIO POR RESPONSABLE</h2>
        <div class="info-row">
            <span class="info-label">Responsable:</span>
            {{ $data['responsable']->nombre_completo }}
        </div>
        <div class="info-row">
            <span class="info-label">Cargo:</span>
            {{ $data['responsable']->cargo ?? 'No especificado' }}
        </div>
        @if($data['responsable']->email)
        <div class="info-row">
            <span class="info-label">Email:</span>
            {{ $data['responsable']->email }}
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%">Artículo</th>
                <th style="width: 30%">Ubicación</th>
                <th style="width: 10%">Cant.</th>
                <th style="width: 35%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $item)
                <tr>
                    <td>{{ $item['articulo'] }}</td>
                    <td>{{ $item['ubicacion_codigo'] }} - {{ $item['ubicacion_nombre'] }}</td>
                    <td style="text-align: center">{{ $item['cantidad'] }}</td>
                    <td class="estado-breakdown">
                        @foreach($item['estados'] as $estado)
                            {{ $estado['label'] }}: {{ $estado['count'] }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">TOTAL</td>
                <td style="text-align: center">{{ $data['total'] }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
@endsection
