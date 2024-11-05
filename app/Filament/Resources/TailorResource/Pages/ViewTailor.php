<?php

namespace App\Filament\Resources\TailorResource\Pages;

use App\Filament\Resources\TailorResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTailor extends ViewRecord
{
    protected static string $resource = TailorResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
