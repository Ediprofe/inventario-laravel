<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudAjusteInventarioResource\Pages;
use App\Models\SolicitudAjusteInventario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SolicitudAjusteInventarioResource extends Resource
{
    protected static ?string $model = SolicitudAjusteInventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Citas de ajuste';

    protected static ?string $modelLabel = 'Solicitud de cita';

    protected static ?string $pluralModelLabel = 'Solicitudes de cita';

    protected static ?string $navigationGroup = 'Auditoría';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Solicitud')
                    ->schema([
                        Forms\Components\Placeholder::make('codigo')
                            ->label('Código')
                            ->content(fn (SolicitudAjusteInventario $record): string => $record->codigo_solicitud),
                        Forms\Components\Placeholder::make('solicitante')
                            ->label('Solicitante')
                            ->content(fn (SolicitudAjusteInventario $record): string => $record->solicitante_nombre),
                        Forms\Components\Placeholder::make('responsable')
                            ->label('Responsable')
                            ->content(fn (SolicitudAjusteInventario $record): string => $record->responsable?->nombre_completo ?? '—'),
                        Forms\Components\Placeholder::make('ubicacion')
                            ->label('Ubicación')
                            ->content(fn (SolicitudAjusteInventario $record): string => $record->ubicacion ? ($record->ubicacion->codigo.' - '.$record->ubicacion->nombre) : '—'),
                        Forms\Components\Placeholder::make('tipo')
                            ->label('Tipo')
                            ->content(fn (SolicitudAjusteInventario $record): string => $record->tipo_solicitud),
                        Forms\Components\Placeholder::make('contacto')
                            ->label('Medio de contacto')
                            ->content(fn (SolicitudAjusteInventario $record): string => match ($record->medio_contacto) {
                                'presencial' => 'Presencial',
                                'whatsapp' => 'WhatsApp',
                                'correo' => 'Correo',
                                'otro' => 'Otro',
                                default => '—',
                            }),
                        Forms\Components\Placeholder::make('contacto_detalle')
                            ->label('Detalle de contacto')
                            ->content(fn (SolicitudAjusteInventario $record): string => $record->contacto_detalle ?: '—'),
                        Forms\Components\Placeholder::make('franja')
                            ->label('Franja sugerida')
                            ->content(fn (SolicitudAjusteInventario $record): string => $record->franja_horaria ?: '—'),
                        Forms\Components\Textarea::make('detalle')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Gestión admin')
                    ->schema([
                        Forms\Components\Select::make('estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'contactado' => 'Contactado',
                                'en_proceso' => 'En proceso',
                                'cerrada' => 'Cerrada',
                                'cancelada' => 'Cancelada',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('observacion_admin')
                            ->label('Observación admin')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('envioInventario'))
            ->columns([
                Tables\Columns\TextColumn::make('codigo_solicitud')
                    ->label('Código')
                    ->sortable(),
                Tables\Columns\TextColumn::make('solicitado_at')
                    ->label('Solicitado')
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsable.nombre_completo')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ubicacion.codigo')
                    ->label('Cód. Ubicación')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_solicitud')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ajuste_general' => 'Ajuste general',
                        'entrada_items' => 'Entrada ítems',
                        'salida_items' => 'Salida ítems',
                        'baja_items' => 'Baja ítems',
                        'mantenimiento' => 'Mantenimiento',
                        'otro' => 'Otro',
                        default => $state,
                    })
                    ->color('info'),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'contactado' => 'primary',
                        'en_proceso' => 'info',
                        'cerrada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('medio_contacto')
                    ->label('Contacto')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'presencial' => 'Presencial',
                        'whatsapp' => 'WhatsApp',
                        'correo' => 'Correo',
                        'otro' => 'Otro',
                        default => '—',
                    }),
                Tables\Columns\TextColumn::make('contacto_detalle')
                    ->label('Detalle contacto')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('confirmado_coordinacion')
                    ->label('Confirmado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('revisadoPor.name')
                    ->label('Revisado por')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('solicitado_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'contactado' => 'Contactado',
                        'en_proceso' => 'En proceso',
                        'cerrada' => 'Cerrada',
                        'cancelada' => 'Cancelada',
                    ]),
                Tables\Filters\SelectFilter::make('tipo_solicitud')
                    ->options([
                        'ajuste_general' => 'Ajuste general',
                        'entrada_items' => 'Entrada ítems',
                        'salida_items' => 'Salida ítems',
                        'baja_items' => 'Baja ítems',
                        'mantenimiento' => 'Mantenimiento',
                        'otro' => 'Otro',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_enlace_cita')
                    ->label('Ver enlace de cita')
                    ->icon('heroicon-o-link')
                    ->url(fn (SolicitudAjusteInventario $record): ?string => $record->envioInventario?->token
                        ? route('inventario.cita-ajuste.mostrar', ['token' => $record->envioInventario->token])
                        : null)
                    ->visible(fn (SolicitudAjusteInventario $record): bool => filled($record->envioInventario?->token))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Eliminar solicitud de cita')
                    ->modalDescription('Se eliminará esta solicitud de la base de datos. También se ajustará el consecutivo para nuevos registros.')
                    ->after(function (): void {
                        SolicitudAjusteInventario::resetConsecutive();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Eliminar solicitudes seleccionadas')
                        ->modalDescription('Se eliminarán de la base de datos y se ajustará el consecutivo para nuevos registros.')
                        ->after(function (): void {
                            SolicitudAjusteInventario::resetConsecutive();
                        }),
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
            'index' => Pages\ListSolicitudAjusteInventarios::route('/'),
            'edit' => Pages\EditSolicitudAjusteInventario::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
