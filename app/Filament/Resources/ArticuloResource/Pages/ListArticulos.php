<?php

namespace App\Filament\Resources\ArticuloResource\Pages;

use App\Exports\ArticulosResumenExport;
use App\Filament\Resources\ArticuloResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListArticulos extends ListRecords
{
    protected static string $resource = ArticuloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportar_resumen')
                ->label('Exportar Resumen')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => Excel::download(
                    new ArticulosResumenExport(),
                    'Resumen_Articulos_' . date('Y-m-d_H-i') . '.xlsx'
                )),
        ];
    }
}
