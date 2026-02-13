<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $returnTo = request()->query('return_to');

        if (is_string($returnTo) && str_starts_with($returnTo, '/')) {
            return $returnTo;
        }

        return static::getResource()::getUrl('index');
    }
}
