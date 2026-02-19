<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    public function mount(): void
    {
        parent::mount();

        $prefill = [
            'sede_id' => request()->integer('sede_id') ?: null,
            'ubicacion_id' => request()->integer('ubicacion_id') ?: null,
            'responsable_id' => request()->integer('responsable_id') ?: null,
        ];

        $prefill = array_filter($prefill, fn ($value) => $value !== null);

        if (! empty($prefill)) {
            $this->form->fill($prefill);
        }
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
