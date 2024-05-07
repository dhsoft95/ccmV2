<?php

namespace App\Filament\Resources\LocationsResource\Pages;

use App\Filament\Resources\LocationsResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLocations extends ManageRecords
{
    protected static string $resource = LocationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
