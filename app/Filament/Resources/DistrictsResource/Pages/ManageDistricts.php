<?php

namespace App\Filament\Resources\DistrictsResource\Pages;

use App\Filament\Imports\DistrictsImporter;
use App\Filament\Imports\RegionsImporter;
use App\Filament\Resources\DistrictsResource;
use Filament\Actions;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDistricts extends ManageRecords
{
    protected static string $resource = DistrictsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
               ImportAction::make()
                   ->importer(DistrictsImporter::class)
        ];
    }
}
