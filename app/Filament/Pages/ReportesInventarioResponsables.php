<?php

namespace App\Filament\Pages;

class ReportesInventarioResponsables extends ReportesInventario
{
    protected string $defaultTab = 'responsable';

    protected static ?string $navigationLabel = 'Inventario por Responsable';

    protected static ?string $title = 'Inventario por Responsable';

    protected static ?string $slug = 'reportes-inventario-responsables';

    protected static ?int $navigationSort = 21;
}
