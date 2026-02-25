<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Enums\Disponibilidad;
use App\Enums\EstadoFisico;
use App\Filament\Resources\ItemResource;
use App\Models\Articulo;
use App\Models\Item;
use App\Models\Responsable;
use App\Models\Sede;
use App\Models\Ubicacion;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Throwable;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    public ?string $returnToUrl = null;

    public ?int $prefillSedeId = null;

    public ?int $prefillUbicacionId = null;

    public ?int $prefillResponsableId = null;

    public function mount(): void
    {
        parent::mount();

        $this->returnToUrl = $this->getSafeReturnToFromQuery();
        $this->prefillSedeId = request()->integer('sede_id') ?: null;
        $this->prefillUbicacionId = request()->integer('ubicacion_id') ?: null;
        $this->prefillResponsableId = request()->integer('responsable_id') ?: null;

        if (request()->boolean('open_batch')) {
            Notification::make()
                ->title('Ubicación lista para agregar ítems')
                ->body('Abra "Agregar Lote". Ya dejamos sede, ubicación y responsable preseleccionados.')
                ->info()
                ->send();

            $this->mountAction('batch_create');
        }
    }

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
                    $path = storage_path('app/public/'.$data['attachment']);

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
                    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InventoryExport, 'Backup_Inventario_'.date('Y-m-d_H-i').'.xlsx');
                }),
            Actions\Action::make('audit_report')
                ->label('Reporte de Auditoría')
                ->color('info')
                ->icon('heroicon-o-clipboard-document-list')
                ->action(function () {
                    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AuditReportExport, 'Inventario_Institucional-'.date('Y-m-d').'.xlsx');
                }),
            Actions\Action::make('batch_create')
                ->label('Agregar Lote')
                ->color('warning')
                ->icon('heroicon-o-squares-plus')
                ->modalWidth('5xl')
                ->form([
                    Section::make('Contexto y datos comunes')
                        ->description('Se aplican a todos los ítems que se creen en este lote.')
                        ->columns(3)
                        ->schema([
                            Select::make('sede_id')
                                ->label('Sede')
                                ->options(Sede::pluck('nombre', 'id'))
                                ->default(fn () => $this->prefillSedeId)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('ubicacion_id', null);
                                    $set('responsable_id', null);
                                }),
                            Select::make('ubicacion_id')
                                ->label('Ubicación')
                                ->options(fn (Get $get) => Ubicacion::where('sede_id', $get('sede_id'))
                                    ->get()
                                    ->mapWithKeys(fn ($ubi) => [$ubi->id => $ubi->codigo.' - '.$ubi->nombre])
                                )
                                ->default(fn () => $this->prefillUbicacionId)
                                ->required()
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (Set $set, ?string $state): void {
                                    if (! $state) {
                                        $set('responsable_id', null);

                                        return;
                                    }

                                    $responsableId = Ubicacion::find((int) $state)?->responsable_id;
                                    $set('responsable_id', $responsableId);
                                }),
                            Select::make('articulo_id')
                                ->label('Artículo')
                                ->options(Articulo::pluck('nombre', 'id'))
                                ->required()
                                ->searchable(),
                            Select::make('responsable_id')
                                ->label('Responsable')
                                ->options(Responsable::query()->pluck('nombre_completo', 'id'))
                                ->default(fn () => $this->prefillResponsableId)
                                ->searchable(),
                            Select::make('estado')
                                ->label('Estado')
                                ->options(EstadoFisico::class)
                                ->required(),
                            Select::make('disponibilidad')
                                ->label('Disponibilidad')
                                ->options(Disponibilidad::class)
                                ->default('en_uso')
                                ->required()
                                ->live(),
                            TextInput::make('marca_comun')
                                ->label('Marca (opcional)')
                                ->placeholder('Ej: Rimax, Genérica, etc.'),
                            Textarea::make('observaciones_comunes')
                                ->label('Observación común (opcional)')
                                ->rows(2)
                                ->columnSpan(2)
                                ->placeholder('Se aplicará a todos los ítems del lote.'),
                        ]),
                    Section::make('Cómo quiere cargar este lote')
                        ->description('Use “rápida” para cantidades altas sin placa o “placas” para pegar lista.')
                        ->schema([
                            Select::make('modo_lote')
                                ->label('Modo de carga')
                                ->options([
                                    'rapida_sin_placa' => 'Rápida: cantidad sin placa',
                                    'placas_linea' => 'Con placas: pegar una por línea',
                                    'detalle_individual' => 'Avanzado: detalle item por item',
                                ])
                                ->default('rapida_sin_placa')
                                ->native(false)
                                ->required()
                                ->live(),
                        ]),
                    Section::make('Carga rápida (sin placa)')
                        ->visible(fn (Get $get): bool => $get('modo_lote') === 'rapida_sin_placa')
                        ->schema([
                            TextInput::make('cantidad')
                                ->label('Cantidad de ítems')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(500)
                                ->default(1)
                                ->required(fn (Get $get): bool => $get('modo_lote') === 'rapida_sin_placa')
                                ->helperText('Ideal para sillas, mesas u otros ítems sin placa.'),
                        ]),
                    Section::make('Carga con placas')
                        ->visible(fn (Get $get): bool => $get('modo_lote') === 'placas_linea')
                        ->schema([
                            Textarea::make('placas_texto')
                                ->label('Placas (una por línea)')
                                ->rows(8)
                                ->required(fn (Get $get): bool => $get('modo_lote') === 'placas_linea')
                                ->placeholder("PLACA-001\nPLACA-002\nPLACA-003")
                                ->helperText('Se validan placas repetidas en el lote y contra el inventario existente.'),
                            Textarea::make('seriales_texto')
                                ->label('Seriales (opcional, una por línea)')
                                ->rows(8)
                                ->placeholder("SERIAL-001\nSERIAL-002\nSERIAL-003")
                                ->helperText('Si diligencia seriales, debe haber la misma cantidad de líneas que placas.'),
                        ]),
                    Section::make('Modo avanzado: detalle item por item')
                        ->visible(fn (Get $get): bool => $get('modo_lote') === 'detalle_individual')
                        ->schema([
                            Repeater::make('items')
                                ->label('')
                                ->columns(4)
                                ->defaultItems(1)
                                ->addActionLabel('+ Agregar otro ítem')
                                ->schema([
                                    TextInput::make('placa')
                                        ->label('Placa'),
                                    TextInput::make('marca')
                                        ->label('Marca'),
                                    TextInput::make('serial')
                                        ->label('Serial'),
                                    TextInput::make('observaciones')
                                        ->label('Observaciones'),
                                ])
                                ->required(fn (Get $get): bool => $get('modo_lote') === 'detalle_individual')
                                ->minItems(1),
                        ]),
                ])
                ->action(function (array $data) {
                    $modo = $data['modo_lote'] ?? 'rapida_sin_placa';
                    $commonData = [
                        'sede_id' => $data['sede_id'],
                        'ubicacion_id' => $data['ubicacion_id'],
                        'articulo_id' => $data['articulo_id'],
                        'responsable_id' => $data['responsable_id'],
                        'estado' => $data['estado'],
                        'disponibilidad' => $data['disponibilidad'],
                        'marca' => $data['marca_comun'] ?? null,
                        'observaciones' => $data['observaciones_comunes'] ?? null,
                    ];

                    try {
                        $count = DB::transaction(function () use ($data, $modo, $commonData) {
                            if ($modo === 'rapida_sin_placa') {
                                $cantidad = max(1, (int) ($data['cantidad'] ?? 1));

                                for ($i = 0; $i < $cantidad; $i++) {
                                    Item::create(array_merge($commonData, [
                                        'placa' => null,
                                        'serial' => null,
                                    ]));
                                }

                                return $cantidad;
                            }

                            if ($modo === 'placas_linea') {
                                $placas = $this->parseLineList($data['placas_texto'] ?? null);
                                if ($placas === []) {
                                    throw new \RuntimeException('Debe ingresar al menos una placa.');
                                }

                                $duplicadas = collect($placas)->duplicates()->unique()->values();
                                if ($duplicadas->isNotEmpty()) {
                                    throw new \RuntimeException('Placas duplicadas en el lote: '.$duplicadas->implode(', '));
                                }

                                $existentes = Item::query()
                                    ->whereIn('placa', $placas)
                                    ->pluck('placa')
                                    ->unique()
                                    ->values();
                                if ($existentes->isNotEmpty()) {
                                    throw new \RuntimeException('Estas placas ya existen en el inventario: '.$existentes->implode(', '));
                                }

                                $seriales = $this->parseLineList($data['seriales_texto'] ?? null);
                                if ($seriales !== [] && count($seriales) !== count($placas)) {
                                    throw new \RuntimeException('Si diligencia seriales, la cantidad de líneas debe coincidir con la cantidad de placas.');
                                }

                                foreach ($placas as $index => $placa) {
                                    Item::create(array_merge($commonData, [
                                        'placa' => $placa,
                                        'serial' => $seriales[$index] ?? null,
                                    ]));
                                }

                                return count($placas);
                            }

                            $items = $data['items'] ?? [];
                            if ($items === []) {
                                throw new \RuntimeException('Debe agregar al menos un ítem en el modo avanzado.');
                            }

                            $placas = collect($items)
                                ->pluck('placa')
                                ->map(fn ($placa) => trim((string) $placa))
                                ->filter(fn ($placa) => $placa !== '' && strtoupper($placa) !== 'NA')
                                ->values();

                            $duplicadas = $placas->duplicates()->unique()->values();
                            if ($duplicadas->isNotEmpty()) {
                                throw new \RuntimeException('Placas duplicadas en el lote: '.$duplicadas->implode(', '));
                            }

                            $existentes = Item::query()
                                ->whereIn('placa', $placas->toArray())
                                ->pluck('placa')
                                ->unique()
                                ->values();
                            if ($existentes->isNotEmpty()) {
                                throw new \RuntimeException('Estas placas ya existen en el inventario: '.$existentes->implode(', '));
                            }

                            $count = 0;
                            foreach ($items as $itemData) {
                                Item::create(array_merge($commonData, [
                                    'placa' => $itemData['placa'] ?? null,
                                    'marca' => $itemData['marca'] ?? ($commonData['marca'] ?? null),
                                    'serial' => $itemData['serial'] ?? null,
                                    'observaciones' => $itemData['observaciones'] ?? ($commonData['observaciones'] ?? null),
                                ]));
                                $count++;
                            }

                            return $count;
                        });
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('No fue posible crear el lote')
                            ->body($exception->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Lote Creado')
                        ->body("Se crearon {$count} ítems correctamente.")
                        ->success()
                        ->send();

                    if ($returnTo = $this->returnToUrl) {
                        $this->redirect($returnTo, navigate: true);
                    }
                }),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function parseLineList(?string $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter(fn ($line) => $line !== '')
            ->values()
            ->all();
    }

    protected function getSafeReturnToFromQuery(): ?string
    {
        $returnTo = request()->query('return_to');

        if (! is_string($returnTo)) {
            return null;
        }

        if (! str_starts_with($returnTo, '/admin/')) {
            return null;
        }

        if (str_starts_with($returnTo, '/livewire/')) {
            return null;
        }

        return $returnTo;
    }
}
