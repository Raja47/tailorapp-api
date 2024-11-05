<?php

namespace App\Filament\Resources\TailorResource\Pages;

use App\Filament\Resources\TailorResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTailors extends ListRecords
{
    protected static string $resource = TailorResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
