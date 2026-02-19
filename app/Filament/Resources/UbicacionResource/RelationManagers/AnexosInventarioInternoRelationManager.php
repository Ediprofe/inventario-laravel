<?php

namespace App\Filament\Resources\UbicacionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AnexosInventarioInternoRelationManager extends RelationManager
{
    protected static string $relationship = 'anexosInventarioInterno';

    protected static ?string $title = 'Inventario complementario de la ubicación';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('titulo')
                    ->label('Nombre del inventario complementario')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Hidden::make('tipo')
                    ->default('complementario')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_corte')
                    ->label('Fecha de corte'),
                Forms\Components\FileUpload::make('archivo_pdf_path')
                    ->label('Documento (PDF, DOCX o XLSX)')
                    ->disk('public')
                    ->directory('ubicaciones/anexos-internos')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/msword',
                        'application/vnd.ms-excel',
                    ])
                    ->maxSize(10240)
                    ->required()
                    ->downloadable()
                    ->openable()
                    ->helperText('Se acepta PDF, DOCX y XLSX. Recomendado PDF para auditoria.'),
                Forms\Components\Toggle::make('adjuntar_en_envio')
                    ->label('Adjuntar en envío de inventario')
                    ->default(true),
                Forms\Components\Toggle::make('activo')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('titulo')
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->searchable()
                    ->label('Inventario complementario'),
                Tables\Columns\TextColumn::make('fecha_corte')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\IconColumn::make('adjuntar_en_envio')
                    ->label('Adjuntar')
                    ->boolean(),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => $this->applyUbicacionResponsableFuente($data)),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_documento')
                    ->label('Ver documento')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => Storage::disk('public')->url($record->archivo_pdf_path))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => $this->applyUbicacionResponsableFuente($data)),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function applyUbicacionResponsableFuente(array $data): array
    {
        $data['responsable_fuente'] = $this->resolveUbicacionResponsable();

        return $data;
    }

    protected function resolveUbicacionResponsable(): ?string
    {
        $owner = $this->getOwnerRecord();
        if (! $owner instanceof Model || ! method_exists($owner, 'responsable')) {
            return null;
        }

        $responsable = $owner->responsable()->first();

        return $responsable?->nombre_completo;
    }
}
