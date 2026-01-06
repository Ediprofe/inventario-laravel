<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SedeResource\Pages;
use App\Filament\Resources\SedeResource\RelationManagers;
use App\Models\Sede;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SedeResource extends Resource
{
    protected static ?string $model = Sede::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('coordinador_id')
                    ->relationship('coordinador', 'nombre_completo')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('direccion'),
                Forms\Components\TextInput::make('telefono'),
                Forms\Components\TextInput::make('email')
                    ->email(),
                Forms\Components\Toggle::make('activo')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('coordinador.nombre_completo')
                    ->label('Coordinador')
                    ->searchable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSedes::route('/'),
            'create' => Pages\CreateSede::route('/create'),
            'edit' => Pages\EditSede::route('/{record}/edit'),
        ];
    }
}
