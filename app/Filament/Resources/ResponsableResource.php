<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResponsableResource\Pages;
use App\Filament\Resources\ResponsableResource\RelationManagers;
use App\Models\Responsable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResponsableResource extends Resource
{
    protected static ?string $model = Responsable::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required(),
                Forms\Components\TextInput::make('apellido')
                    ->required(),
                Forms\Components\Select::make('tipo_documento')
                     ->options([
                        'CC' => 'Cédula de Ciudadanía',
                        'TI' => 'Tarjeta de Identidad',
                        'CE' => 'Cédula de Extranjería',
                        'PASAPORTE' => 'Pasaporte',
                     ]),
                Forms\Components\TextInput::make('documento'),
                Forms\Components\TextInput::make('cargo'),
                Forms\Components\TextInput::make('email')
                    ->email(),
                Forms\Components\TextInput::make('telefono'),
                Forms\Components\Select::make('sede_id')
                    ->relationship('sede', 'nombre')
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true),
                Forms\Components\Toggle::make('es_firmante_entrega')
                    ->label('Firmante de entrega por defecto')
                    ->helperText('Si activa esta opción, este responsable quedará como firmante de entrega/verificación en los reportes.')
                    ->live()
                    ->default(false),
                ViewField::make('firma_entrega_link')
                    ->label('Firmar desde tablet/celular')
                    ->view('filament.forms.components.firma-entrega-link')
                    ->helperText('Genere y abra el enlace en la tablet para capturar la firma de entrega.')
                    ->visible(fn (Get $get): bool => (bool) $get('es_firmante_entrega'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->searchable(['nombre', 'apellido'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('documento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cargo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sede.nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
                Tables\Columns\IconColumn::make('es_firmante_entrega')
                    ->label('Firma entrega')
                    ->boolean(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sede')
                    ->relationship('sede', 'nombre'),
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
            'index' => Pages\ListResponsables::route('/'),
            'create' => Pages\CreateResponsable::route('/create'),
            'edit' => Pages\EditResponsable::route('/{record}/edit'),
        ];
    }
}
