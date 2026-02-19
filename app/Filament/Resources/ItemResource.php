<?php

namespace App\Filament\Resources;

use App\Enums\Disponibilidad;
use App\Enums\EstadoFisico;
use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Disable global search for this resource to prevent memory exhaustion
    // with 5,000+ records and multiple searchable relationship columns
    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sede_id')
                    ->relationship('sede', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('ubicacion_id')
                    ->relationship('ubicacion', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('articulo_id')
                    ->relationship('articulo', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('responsable_id')
                    ->relationship('responsable', 'nombre_completo')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('placa')
                    ->unique(table: 'items', column: 'placa', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where(fn ($query) => $query->whereNotNull('placa')->where('placa', '!=', '')->where('placa', '!=', 'NA'));
                    })
                    ->validationMessages([
                        'unique' => 'Ya existe un ítem con esta placa.',
                    ]),
                Forms\Components\TextInput::make('marca'),
                Forms\Components\TextInput::make('serial'),
                Forms\Components\Select::make('estado')
                    ->options(\App\Enums\EstadoFisico::class)
                    ->required(),
                Forms\Components\Select::make('disponibilidad')
                    ->options(\App\Enums\Disponibilidad::class)
                    ->required(),
                Forms\Components\Textarea::make('descripcion')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('observaciones')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('placa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('articulo.nombre')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sede.nombre')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ubicacion.codigo')
                    ->label('Cód. Ubicación') // Short label
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ubicacion.nombre')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsable.nombre_completo')
                    ->label('Responsable')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('marca')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('serial')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->formatStateUsing(fn (EstadoFisico|string|null $state): string => $state instanceof EstadoFisico
                        ? $state->getLabel()
                        : (EstadoFisico::tryFrom((string) $state)?->getLabel() ?? 'Sin Estado'))
                    ->color(fn (EstadoFisico|string|null $state): string|array|null => $state instanceof EstadoFisico
                        ? $state->getColor()
                        : EstadoFisico::tryFrom((string) $state)?->getColor())
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('disponibilidad')
                    ->label('Disponibilidad')
                    ->formatStateUsing(fn (Disponibilidad|string|null $state): string => $state instanceof Disponibilidad
                        ? $state->getLabel()
                        : (Disponibilidad::tryFrom((string) $state)?->getLabel() ?? 'Sin definir'))
                    ->color(fn (Disponibilidad|string|null $state): string|array|null => $state instanceof Disponibilidad
                        ? $state->getColor()
                        : Disponibilidad::tryFrom((string) $state)?->getColor())
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('observaciones')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->since()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modificado')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->defaultPaginationPageOption(50)
            ->paginationPageOptions([25, 50, 100])
            ->filters([
                Tables\Filters\SelectFilter::make('sede')
                    ->relationship('sede', 'nombre'),
                Tables\Filters\SelectFilter::make('articulo')
                    ->relationship('articulo', 'nombre')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('estado')
                    ->options(\App\Enums\EstadoFisico::class),
                Tables\Filters\SelectFilter::make('disponibilidad')
                    ->options(\App\Enums\Disponibilidad::class)
                    ->default('en_uso'), // Pre-selected by default, but user can change
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('editar_en_lote')
                        ->label('Editar en lote')
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary')
                        ->modalHeading('Edición masiva de ítems')
                        ->modalDescription('Seleccione qué campos desea actualizar para los ítems seleccionados.')
                        ->form([
                            Forms\Components\Section::make('Ubicación y responsable')
                                ->schema([
                                    Forms\Components\Toggle::make('actualizar_ubicacion')
                                        ->label('Actualizar ubicación/sede')
                                        ->helperText('Actualiza la ubicación y la sede automáticamente según la ubicación elegida.')
                                        ->live(),
                                    Forms\Components\Select::make('sede_filtro_id')
                                        ->label('Filtrar ubicaciones por sede (opcional)')
                                        ->options(\App\Models\Sede::query()->pluck('nombre', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_ubicacion'))
                                        ->live(),
                                    Forms\Components\Select::make('ubicacion_id')
                                        ->label('Nueva ubicación')
                                        ->options(fn (Forms\Get $get) => \App\Models\Ubicacion::query()
                                            ->when($get('sede_filtro_id'), fn ($q, $sedeId) => $q->where('sede_id', $sedeId))
                                            ->orderBy('codigo')
                                            ->get()
                                            ->mapWithKeys(fn ($ubi) => [$ubi->id => $ubi->codigo.' - '.$ubi->nombre]))
                                        ->searchable()
                                        ->required(fn (Forms\Get $get): bool => (bool) $get('actualizar_ubicacion'))
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_ubicacion')),
                                    Forms\Components\Toggle::make('asignar_responsable_ubicacion')
                                        ->label('Asignar responsable de la ubicación destino')
                                        ->default(true)
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_ubicacion')),
                                    Forms\Components\Toggle::make('actualizar_responsable')
                                        ->label('Actualizar responsable manualmente')
                                        ->helperText('Si activa esta opción, este valor tendrá prioridad sobre el responsable de la ubicación.')
                                        ->live(),
                                    Forms\Components\Select::make('responsable_id')
                                        ->label('Nuevo responsable')
                                        ->options(fn () => ['__null__' => 'Sin responsable'] + \App\Models\Responsable::query()
                                            ->orderBy('nombre')
                                            ->get()
                                            ->mapWithKeys(fn ($responsable) => [$responsable->id => $responsable->nombre_completo])
                                            ->toArray())
                                        ->searchable()
                                        ->required(fn (Forms\Get $get): bool => (bool) $get('actualizar_responsable'))
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_responsable')),
                                ])
                                ->columns(2),
                            Forms\Components\Section::make('Datos del ítem')
                                ->schema([
                                    Forms\Components\Toggle::make('actualizar_articulo')
                                        ->label('Actualizar artículo')
                                        ->live(),
                                    Forms\Components\Select::make('articulo_id')
                                        ->label('Nuevo artículo')
                                        ->options(fn () => \App\Models\Articulo::query()
                                            ->orderBy('nombre')
                                            ->pluck('nombre', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required(fn (Forms\Get $get): bool => (bool) $get('actualizar_articulo'))
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_articulo')),
                                    Forms\Components\Toggle::make('actualizar_estado')
                                        ->label('Actualizar estado')
                                        ->live(),
                                    Forms\Components\Select::make('estado')
                                        ->label('Nuevo estado')
                                        ->options(\App\Enums\EstadoFisico::class)
                                        ->required(fn (Forms\Get $get): bool => (bool) $get('actualizar_estado'))
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_estado')),
                                    Forms\Components\Toggle::make('actualizar_disponibilidad')
                                        ->label('Actualizar disponibilidad')
                                        ->live(),
                                    Forms\Components\Select::make('disponibilidad')
                                        ->label('Nueva disponibilidad')
                                        ->options(\App\Enums\Disponibilidad::class)
                                        ->required(fn (Forms\Get $get): bool => (bool) $get('actualizar_disponibilidad'))
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_disponibilidad')),
                                    Forms\Components\Toggle::make('actualizar_placa')
                                        ->label('Actualizar placa')
                                        ->helperText('Evite aplicar una misma placa real a varios ítems. Use NA o vacío si aplica.')
                                        ->live(),
                                    Forms\Components\TextInput::make('placa')
                                        ->label('Nueva placa')
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_placa')),
                                    Forms\Components\Toggle::make('actualizar_marca')
                                        ->label('Actualizar marca')
                                        ->live(),
                                    Forms\Components\TextInput::make('marca')
                                        ->label('Nueva marca')
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_marca')),
                                    Forms\Components\Toggle::make('actualizar_serial')
                                        ->label('Actualizar serial')
                                        ->live(),
                                    Forms\Components\TextInput::make('serial')
                                        ->label('Nuevo serial')
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_serial')),
                                ])
                                ->columns(2),
                            Forms\Components\Section::make('Textos largos')
                                ->schema([
                                    Forms\Components\Toggle::make('actualizar_descripcion')
                                        ->label('Actualizar descripción')
                                        ->live(),
                                    Forms\Components\Textarea::make('descripcion')
                                        ->label('Nueva descripción')
                                        ->rows(3)
                                        ->columnSpanFull()
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_descripcion')),
                                    Forms\Components\Toggle::make('actualizar_observaciones')
                                        ->label('Actualizar observaciones')
                                        ->live(),
                                    Forms\Components\Textarea::make('observaciones')
                                        ->label('Nuevas observaciones')
                                        ->rows(3)
                                        ->columnSpanFull()
                                        ->visible(fn (Forms\Get $get): bool => (bool) $get('actualizar_observaciones')),
                                ]),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data): void {
                            $updates = [];

                            if (($data['actualizar_ubicacion'] ?? false) && ! empty($data['ubicacion_id'])) {
                                $ubicacion = \App\Models\Ubicacion::find($data['ubicacion_id']);

                                if (! $ubicacion) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Ubicación no válida')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $updates['ubicacion_id'] = $ubicacion->id;
                                $updates['sede_id'] = $ubicacion->sede_id;

                                if (($data['asignar_responsable_ubicacion'] ?? false)) {
                                    $updates['responsable_id'] = $ubicacion->responsable_id;
                                }
                            }

                            if (($data['actualizar_responsable'] ?? false)) {
                                $updates['responsable_id'] = ($data['responsable_id'] ?? null) === '__null__'
                                    ? null
                                    : ($data['responsable_id'] ?? null);
                            }

                            if (($data['actualizar_articulo'] ?? false)) {
                                $updates['articulo_id'] = $data['articulo_id'] ?? null;
                            }

                            if (($data['actualizar_estado'] ?? false)) {
                                $updates['estado'] = $data['estado'] ?? null;
                            }

                            if (($data['actualizar_disponibilidad'] ?? false)) {
                                $updates['disponibilidad'] = $data['disponibilidad'] ?? null;
                            }

                            if (($data['actualizar_placa'] ?? false)) {
                                $placa = trim((string) ($data['placa'] ?? ''));

                                if ($records->count() > 1 && $placa !== '' && strtoupper($placa) !== 'NA') {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Placa no aplicada')
                                        ->body('No es seguro asignar la misma placa real a múltiples ítems. Use NA o actualice placa de forma individual.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $updates['placa'] = $placa;
                            }

                            if (($data['actualizar_marca'] ?? false)) {
                                $updates['marca'] = trim((string) ($data['marca'] ?? ''));
                            }

                            if (($data['actualizar_serial'] ?? false)) {
                                $updates['serial'] = trim((string) ($data['serial'] ?? ''));
                            }

                            if (($data['actualizar_descripcion'] ?? false)) {
                                $updates['descripcion'] = trim((string) ($data['descripcion'] ?? ''));
                            }

                            if (($data['actualizar_observaciones'] ?? false)) {
                                $updates['observaciones'] = trim((string) ($data['observaciones'] ?? ''));
                            }

                            if (count($updates) === 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Sin cambios')
                                    ->body('Seleccione al menos un campo para actualizar en lote.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            \Illuminate\Support\Facades\DB::transaction(function () use ($records, $updates): void {
                                $records->each(fn ($item) => $item->update($updates));
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Edición masiva completada')
                                ->body(count($records).' ítem(s) actualizado(s).')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('cambiar_ubicacion')
                        ->label('Cambiar Ubicación')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('sede_id')
                                ->label('Nueva Sede')
                                ->options(\App\Models\Sede::pluck('nombre', 'id'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Forms\Set $set) => $set('ubicacion_id', null)),
                            Forms\Components\Select::make('ubicacion_id')
                                ->label('Nueva Ubicación')
                                ->options(fn (Forms\Get $get) => \App\Models\Ubicacion::where('sede_id', $get('sede_id'))
                                    ->get()
                                    ->mapWithKeys(fn ($ubi) => [$ubi->id => $ubi->codigo.' - '.$ubi->nombre])
                                )
                                ->required()
                                ->searchable(),
                            Forms\Components\Toggle::make('asignar_responsable')
                                ->label('Asignar también el responsable de la ubicación destino')
                                ->default(true)
                                ->helperText('Si activa, los ítems quedarán a cargo del responsable asignado a la nueva ubicación.'),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data) {
                            $ubicacion = \App\Models\Ubicacion::find($data['ubicacion_id']);

                            $updateData = [
                                'sede_id' => $data['sede_id'],
                                'ubicacion_id' => $data['ubicacion_id'],
                            ];

                            if ($data['asignar_responsable'] && $ubicacion?->responsable_id) {
                                $updateData['responsable_id'] = $ubicacion->responsable_id;
                            }

                            $records->each(fn ($item) => $item->update($updateData));

                            \Filament\Notifications\Notification::make()
                                ->title('Ubicación actualizada')
                                ->body(count($records).' ítem(s) movido(s) a '.$ubicacion->nombre)
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
