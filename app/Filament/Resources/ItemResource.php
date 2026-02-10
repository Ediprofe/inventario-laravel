<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('disponibilidad')
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
                                ->options(fn (Forms\Get $get) => 
                                    \App\Models\Ubicacion::where('sede_id', $get('sede_id'))
                                        ->get()
                                        ->mapWithKeys(fn ($ubi) => [$ubi->id => $ubi->codigo . ' - ' . $ubi->nombre])
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
                                ->body(count($records) . ' ítem(s) movido(s) a ' . $ubicacion->nombre)
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
