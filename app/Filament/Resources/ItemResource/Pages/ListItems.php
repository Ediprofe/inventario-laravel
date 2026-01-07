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
            Actions\Action::make('batch_create')
                ->label('Agregar Lote')
                ->color('warning')
                ->icon('heroicon-o-squares-plus')
                ->modalWidth('5xl')
                ->form([
                    \Filament\Forms\Components\Section::make('Datos Comunes')
                        ->description('Estos datos se aplicarán a todos los ítems del lote')
                        ->columns(3)
                        ->schema([
                            \Filament\Forms\Components\Select::make('sede_id')
                                ->label('Sede')
                                ->options(\App\Models\Sede::pluck('nombre', 'id'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (\Filament\Forms\Set $set) => $set('ubicacion_id', null)),
                            \Filament\Forms\Components\Select::make('ubicacion_id')
                                ->label('Ubicación')
                                ->options(fn (\Filament\Forms\Get $get) => 
                                    \App\Models\Ubicacion::where('sede_id', $get('sede_id'))
                                        ->get()
                                        ->mapWithKeys(fn ($ubi) => [$ubi->id => $ubi->codigo . ' - ' . $ubi->nombre])
                                )
                                ->required()
                                ->searchable(),
                            \Filament\Forms\Components\Select::make('articulo_id')
                                ->label('Artículo')
                                ->options(\App\Models\Articulo::pluck('nombre', 'id'))
                                ->required()
                                ->searchable(),
                            \Filament\Forms\Components\Select::make('responsable_id')
                                ->label('Responsable')
                                ->options(\App\Models\Responsable::all()->pluck('nombre_completo', 'id'))
                                ->searchable(),
                            \Filament\Forms\Components\Select::make('estado')
                                ->label('Estado')
                                ->options(\App\Enums\EstadoFisico::class)
                                ->required(),
                            \Filament\Forms\Components\Select::make('disponibilidad')
                                ->label('Disponibilidad')
                                ->options(\App\Enums\Disponibilidad::class)
                                ->default('en_uso')
                                ->required(),
                        ]),
                    \Filament\Forms\Components\Section::make('Ítems Individuales')
                        ->description('Ingresa los datos únicos de cada ítem')
                        ->schema([
                            \Filament\Forms\Components\Repeater::make('items')
                                ->label('')
                                ->columns(4)
                                ->defaultItems(1)
                                ->addActionLabel('+ Agregar otro ítem')
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('placa')
                                        ->label('Placa'),
                                    \Filament\Forms\Components\TextInput::make('marca')
                                        ->label('Marca'),
                                    \Filament\Forms\Components\TextInput::make('serial')
                                        ->label('Serial'),
                                    \Filament\Forms\Components\TextInput::make('observaciones')
                                        ->label('Observaciones'),
                                ])
                                ->required()
                                ->minItems(1),
                        ]),
                ])
                ->action(function (array $data) {
                    $commonData = [
                        'sede_id' => $data['sede_id'],
                        'ubicacion_id' => $data['ubicacion_id'],
                        'articulo_id' => $data['articulo_id'],
                        'responsable_id' => $data['responsable_id'],
                        'estado' => $data['estado'],
                        'disponibilidad' => $data['disponibilidad'],
                    ];
                    
                    $count = 0;
                    foreach ($data['items'] as $itemData) {
                        \App\Models\Item::create(array_merge($commonData, [
                            'placa' => $itemData['placa'] ?? null,
                            'marca' => $itemData['marca'] ?? null,
                            'serial' => $itemData['serial'] ?? null,
                            'observaciones' => $itemData['observaciones'] ?? null,
                        ]));
                        $count++;
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Lote Creado')
                        ->body("Se crearon {$count} ítems correctamente.")
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * Apply default en_uso filter when disponibilidad filter is not explicitly set.
     * This ensures consistency with Reportes which always filter by en_uso.
     */
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getTableQuery();
        
        // Check if the disponibilidad filter is explicitly set in the URL
        $disponibilidadFilter = $this->tableFilters['disponibilidad']['value'] ?? null;
        
        // If not set (null) or empty, apply default en_uso filter
        if (empty($disponibilidadFilter)) {
            $query->where('disponibilidad', \App\Enums\Disponibilidad::EN_USO);
        }
        
        return $query;
    }
}
