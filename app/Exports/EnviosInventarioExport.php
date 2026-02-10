<?php

namespace App\Exports;

use App\Models\EnvioInventario;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use App\Exports\Concerns\DefaultTableStyles;

class EnviosInventarioExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize, WithColumnWidths
{
    use DefaultTableStyles;

    public function columnWidths(): array
    {
        return [
            'I' => 50, // Observaciones
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
            'Fecha Envío',
            'Estado',
            'Fecha Aprobación',
            'IP Aprobación',
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
            $envio->enviado_at?->format('Y-m-d H:i'),
            $envio->estaAprobado() ? 'Aprobado' : 'Pendiente',
            $envio->aprobado_at?->format('Y-m-d H:i') ?? '—',
            $envio->ip_aprobacion ?? '—',
            $envio->observaciones ?? '',
        ];
    }

    public function title(): string
    {
        return 'Envíos Inventario';
    }
}
