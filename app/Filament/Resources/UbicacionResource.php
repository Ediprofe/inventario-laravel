<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UbicacionResource\Pages;
use App\Filament\Resources\UbicacionResource\RelationManagers\AnexosInventarioInternoRelationManager;
use App\Models\Ubicacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UbicacionResource extends Resource
{
    protected static ?string $model = Ubicacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralModelLabel = 'Ubicaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sede_id')
                    ->relationship('sede', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('nombre')
                    ->required(),
                Forms\Components\TextInput::make('codigo')
                    ->required(),
                Forms\Components\Select::make('tipo')
                    ->options(\App\Enums\TipoUbicacion::class)
                    ->required(),
                Forms\Components\Select::make('responsable_id')
                    ->relationship('responsable', 'nombre_completo')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('piso')
                    ->numeric(),
                Forms\Components\TextInput::make('capacidad')
                    ->numeric(),
                Forms\Components\Textarea::make('observaciones')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sede.nombre')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsable.nombre_completo')
                    ->label('Responsable'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sede')
                    ->relationship('sede', 'nombre'),
                Tables\Filters\SelectFilter::make('tipo')
                    ->options(\App\Enums\TipoUbicacion::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reasignar')
                    ->label('Reasignar Todo')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->modalHeading('Transferir Inventario de Ubicación')
                    ->form(function (Ubicacion $record) {
                        // Check for laptops in this location
                        $portatiles = $record->items()
                            ->whereHas('articulo', fn ($q) => $q->where('nombre', 'like', '%portatil%')
                                ->orWhere('nombre', 'like', '%portátil%')
                                ->orWhere('nombre', 'like', '%laptop%'))
                            ->with(['articulo', 'responsable'])
                            ->get();

                        $formFields = [
                            Forms\Components\Select::make('nuevo_responsable_id')
                                ->label('Nuevo Responsable')
                                ->options(\App\Models\Responsable::all()->pluck('nombre_completo', 'id'))
                                ->searchable()
                                ->required(),
                        ];

                        if ($portatiles->count() > 0) {
                            $formFields[] = Forms\Components\Placeholder::make('aviso_portatiles')
                                ->label('')
                                ->content('⚠️ Se detectaron '.$portatiles->count().' portátiles en esta ubicación. Seleccione cuáles desea transferir:');

                            $formFields[] = Forms\Components\CheckboxList::make('portatiles_a_transferir')
                                ->label('Portátiles')
                                ->options(
                                    $portatiles->mapWithKeys(fn ($item) => [
                                        $item->id => $item->articulo->nombre.
                                            ' | Placa: '.($item->placa ?: 'N/A').
                                            ' | Resp: '.($item->responsable?->nombre_completo ?: 'Sin asignar'),
                                    ])->toArray()
                                )
                                ->columns(1)
                                ->bulkToggleable()
                                ->helperText('Los portátiles NO seleccionados mantendrán su responsable actual.');
                        }

                        return $formFields;
                    })
                    ->action(function (Ubicacion $record, array $data) {
                        // Get IDs of laptops to EXCLUDE (all laptops minus selected ones)
                        $todosPortatiles = $record->items()
                            ->whereHas('articulo', fn ($q) => $q->where('nombre', 'like', '%portatil%')
                                ->orWhere('nombre', 'like', '%portátil%')
                                ->orWhere('nombre', 'like', '%laptop%'))
                            ->pluck('id')
                            ->toArray();

                        $portatilesATransferir = $data['portatiles_a_transferir'] ?? [];
                        $portatilesAExcluir = array_diff($todosPortatiles, $portatilesATransferir);

                        // Update all items EXCEPT excluded laptops
                        $count = $record->items()
                            ->whereNotIn('id', $portatilesAExcluir)
                            ->update([
                                'responsable_id' => $data['nuevo_responsable_id'],
                            ]);

                        \Filament\Notifications\Notification::make()
                            ->title("Se transfirieron {$count} ítems")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AnexosInventarioInternoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUbicacions::route('/'),
            'create' => Pages\CreateUbicacion::route('/create'),
            'edit' => Pages\EditUbicacion::route('/{record}/edit'),
        ];
    }
}
