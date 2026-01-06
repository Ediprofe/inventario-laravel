<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Importar Excel (Reset)')
                ->color('danger')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('attachment')
                        ->label('Archivo Excel (Items, Sedes, etc.)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required(),
                ])
                ->action(function (array $data, \App\Services\ResetImportService $service) {
                    $path = storage_path('app/public/' . $data['attachment']);
                    
                    try {
                        $result = $service->import($path);
                        \Filament\Notifications\Notification::make()
                            ->title('Importación Completada')
                            ->body($result['message'])
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Error en Importación')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
            Actions\Action::make('export')
                ->label('Exportar Backup')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InventoryExport(), 'Backup_Inventario_' . date('Y-m-d_H-i') . '.xlsx');
                }),
            Actions\Action::make('audit_report')
                ->label('Reporte de Auditoría')
                ->color('info')
                ->icon('heroicon-o-clipboard-document-list')
                ->action(function () {
                    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AuditReportExport(), 'Inventario_Institucional-' . date('Y-m-d') . '.xlsx');
                }),
        ];
    }
}
