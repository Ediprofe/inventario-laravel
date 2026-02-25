<?php

namespace App\Filament\Resources;

use App\Enums\Disponibilidad;
use App\Enums\EstadoFisico;
use App\Filament\Resources\ArticuloResource\Pages;
use App\Models\Articulo;
use App\Models\Sede;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticuloResource extends Resource
{
    protected static ?string $model = Articulo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('categoria')
                    ->options(\App\Enums\CategoriaArticulo::class)
                    ->required(),
                Forms\Components\TextInput::make('codigo')
                    ->unique(ignoreRecord: true)
                    ->helperText('Si lo deja vacío, se genera automáticamente al guardar.'),
                Forms\Components\Textarea::make('descripcion')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('foto_path')
                    ->label('Foto del artículo')
                    ->disk('public')
                    ->directory('articulos')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/heic',
                        'image/heif',
                        'image/heic-sequence',
                        'image/heif-sequence',
                    ])
                    ->maxSize(10240)
                    ->rules([
                        'mimes:jpg,jpeg,png,webp,heic,heif',
                    ])
                    ->validationMessages([
                        'mimes' => 'Formato no compatible. Use JPG, PNG, WEBP o HEIC/HEIF.',
                        'max' => 'La imagen supera el tamaño permitido (10 MB).',
                    ])
                    ->openable()
                    ->downloadable()
                    ->helperText('Opcional. Se optimiza automáticamente a WEBP. Soporta JPG/PNG/WEBP y HEIC/HEIF (si el servidor tiene Imagick).')
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
                Tables\Columns\ImageColumn::make('foto_path')
                    ->label('Foto')
                    ->disk('public')
                    ->square(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Total')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_principal_count')
                    ->label('Principal')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_escuelita_count')
                    ->label('Escuelita')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('disponibilidad_resumen')
                    ->label('Disponibilidad')
                    ->state(fn (Articulo $record): string => static::formatDisponibilidadSummary($record)),
                Tables\Columns\TextColumn::make('estado_resumen')
                    ->label('Estado')
                    ->state(fn (Articulo $record): string => static::formatEstadoSummary($record)),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')
                    ->options(\App\Enums\CategoriaArticulo::class),
                Tables\Filters\SelectFilter::make('sede_items')
                    ->label('Tiene ítems en sede')
                    ->options(fn () => Sede::query()->orderBy('nombre')->pluck('nombre', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('items', fn (Builder $itemsQuery) => $itemsQuery
                            ->where('sede_id', $data['value']));
                    }),
                Tables\Filters\SelectFilter::make('disponibilidad_items')
                    ->label('Tiene items con disponibilidad')
                    ->options(\App\Enums\Disponibilidad::class)
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('items', fn (Builder $itemsQuery) => $itemsQuery
                            ->where('disponibilidad', $data['value']));
                    }),
                Tables\Filters\SelectFilter::make('estado_items')
                    ->label('Tiene items con estado')
                    ->options(\App\Enums\EstadoFisico::class)
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('items', fn (Builder $itemsQuery) => $itemsQuery
                            ->where('estado', $data['value']));
                    }),
                Tables\Filters\TernaryFilter::make('sin_items')
                    ->label('Relación con ítems')
                    ->placeholder('Todos')
                    ->trueLabel('Sin ítems')
                    ->falseLabel('Con ítems')
                    ->queries(
                        true: fn (Builder $query) => $query->doesntHave('items'),
                        false: fn (Builder $query) => $query->has('items'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->defaultSort('nombre')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(static::withCountDefinitions());
    }

    public static function withCountDefinitions(): array
    {
        return [
            'items',
            'items as items_principal_count' => fn (Builder $query) => $query->whereHas('sede', fn (Builder $sedeQuery) => $sedeQuery->whereRaw('lower(nombre) = ?', ['principal'])),
            'items as items_escuelita_count' => fn (Builder $query) => $query->whereHas('sede', fn (Builder $sedeQuery) => $sedeQuery->whereRaw('lower(nombre) = ?', ['escuelita'])),
            'items as items_en_uso_count' => fn (Builder $query) => $query->where('disponibilidad', Disponibilidad::EN_USO->value),
            'items as items_de_baja_count' => fn (Builder $query) => $query->where('disponibilidad', Disponibilidad::DE_BAJA->value),
            'items as items_en_reparacion_count' => fn (Builder $query) => $query->where('disponibilidad', Disponibilidad::EN_REPARACION->value),
            'items as items_extraviado_count' => fn (Builder $query) => $query->where('disponibilidad', Disponibilidad::EXTRAVIADO->value),
            'items as items_bueno_count' => fn (Builder $query) => $query->where('estado', EstadoFisico::BUENO->value),
            'items as items_regular_count' => fn (Builder $query) => $query->where('estado', EstadoFisico::REGULAR->value),
            'items as items_malo_count' => fn (Builder $query) => $query->where('estado', EstadoFisico::MALO->value),
            'items as items_sin_estado_count' => fn (Builder $query) => $query->where('estado', EstadoFisico::SIN_ESTADO->value),
        ];
    }

    public static function formatDisponibilidadSummary(Articulo $record): string
    {
        return sprintf(
            'Uso: %d | Baja: %d | Reparación: %d | Extraviado: %d',
            (int) $record->items_en_uso_count,
            (int) $record->items_de_baja_count,
            (int) $record->items_en_reparacion_count,
            (int) $record->items_extraviado_count,
        );
    }

    public static function formatEstadoSummary(Articulo $record): string
    {
        return sprintf(
            'Bueno: %d | Regular: %d | Malo: %d | Sin estado: %d',
            (int) $record->items_bueno_count,
            (int) $record->items_regular_count,
            (int) $record->items_malo_count,
            (int) $record->items_sin_estado_count,
        );
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
            'index' => Pages\ListArticulos::route('/'),
            'create' => Pages\CreateArticulo::route('/create'),
            'edit' => Pages\EditArticulo::route('/{record}/edit'),
        ];
    }
}
