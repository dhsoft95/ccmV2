<?php

namespace App\Filament\Resources\SupportersResource\Pages;

use App\Filament\Resources\SupportersResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSupporters extends ManageRecords
{
    protected static string $resource = SupportersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
