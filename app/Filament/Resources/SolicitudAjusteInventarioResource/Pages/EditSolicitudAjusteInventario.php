<?php

namespace App\Filament\Resources\SolicitudAjusteInventarioResource\Pages;

use App\Filament\Resources\SolicitudAjusteInventarioResource;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudAjusteInventario extends EditRecord
{
    protected static string $resource = SolicitudAjusteInventarioResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['revisado_por_user_id'] = auth()->id();
        $data['revisado_at'] = now();

        return $data;
    }
}
