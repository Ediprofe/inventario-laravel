<?php

namespace App\Exports;

use App\Exports\Concerns\DefaultTableStyles;
use App\Models\EnvioInventario;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

class EnviosInventarioExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use DefaultTableStyles;

    public function columnWidths(): array
    {
        return [
            'K' => 50, // Observaciones
        ];
    }

    public function query()
    {
        return EnvioInventario::query()
            ->with(['responsable', 'ubicacion'])
            ->orderBy('enviado_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Responsable',
            'Tipo',
            'Ubicación',
            'Email Enviado A',
            'Firmante',
            'Fecha Envío',
            'Estado',
            'Fecha Firma',
            'IP Firma',
            'Firma Registrada',
            'Observaciones',
        ];
    }

    public function map($envio): array
    {
        return [
            $envio->responsable?->nombre_completo,
            $envio->tipo === 'por_ubicacion' ? 'Por Ubicación' : 'Por Responsable',
            $envio->ubicacion?->nombre ?? '—',
            $envio->email_enviado_a,
            $envio->firmante_nombre ?? '—',
            $envio->enviado_at?->format('Y-m-d H:i'),
            $envio->estaAprobado() ? 'Firmado' : 'Pendiente de firma',
            $envio->aprobado_at?->format('Y-m-d H:i') ?? '—',
            $envio->ip_aprobacion ?? '—',
            $envio->firma_base64 ? 'Sí' : 'No',
            $envio->observaciones ?? '',
        ];
    }

    public function title(): string
    {
        return 'Envíos Inventario';
    }
}
