<?php

namespace App\Filament\Pages;

class ReportesInventarioConsolidado extends ReportesInventario
{
    protected string $defaultTab = 'consolidado';

    protected static ?string $navigationLabel = 'Inventario Consolidado';

    protected static ?string $title = 'Inventario Consolidado';

    protected static ?string $slug = 'reportes-inventario-consolidado';

    protected static ?int $navigationSort = 22;
}
