<?php

namespace App\Filament\Resources\RegionsResource\Pages;

use App\Filament\Imports\RegionsImporter;
use App\Filament\Resources\RegionsResource;
use App\Imports\DistrictsImport;
use App\Imports\RegionsImport;
use Filament\Actions;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRegions extends ManageRecords
{
    protected static string $resource = RegionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
                ->importer(RegionsImporter::class)
        ];
    }
}
