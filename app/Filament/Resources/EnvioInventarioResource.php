<?php

namespace App\Filament\Resources;

use App\Models\EnvioInventario;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\EnvioInventarioResource\Pages;

class EnvioInventarioResource extends Resource
{
    protected static ?string $model = EnvioInventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Envíos Inventario';

    protected static ?string $modelLabel = 'Envío de Inventario';

    protected static ?string $pluralModelLabel = 'Envíos de Inventario';

    protected static ?string $navigationGroup = 'Auditoría';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('responsable.nombre_completo')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'por_ubicacion' => '📍 Por Ubicación',
                        'por_responsable' => '👤 Por Responsable',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'por_ubicacion' => 'info',
                        'por_responsable' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('email_enviado_a')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('enviado_at')
                    ->label('Enviado')
                    ->since()
                    ->sortable(),
                Tables\Columns\IconColumn::make('aprobado_at')
                    ->label('Estado')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->tooltip(fn ($record) => $record->estaAprobado()
                        ? 'Aprobado el ' . $record->aprobado_at->format('d/m/Y H:i')
                        : 'Pendiente de aprobación'),
                Tables\Columns\TextColumn::make('aprobado_at')
                    ->label('Aprobado')
                    ->since()
                    ->placeholder('Pendiente')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->limit(30)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('enviado_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => '⏳ Pendiente',
                        'aprobado' => '✅ Aprobado',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value']) {
                            'pendiente' => $query->whereNull('aprobado_at'),
                            'aprobado' => $query->whereNotNull('aprobado_at'),
                            default => $query,
                        };
                    }),
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'por_ubicacion' => '📍 Por Ubicación',
                        'por_responsable' => '👤 Por Responsable',
                    ]),
                Tables\Filters\SelectFilter::make('responsable')
                    ->relationship('responsable', 'nombre_completo')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_aprobacion')
                    ->label('Ver enlace')
                    ->icon('heroicon-o-link')
                    ->url(fn (EnvioInventario $record) => url("/inventario/aprobar/{$record->token}"))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnviosInventario::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
