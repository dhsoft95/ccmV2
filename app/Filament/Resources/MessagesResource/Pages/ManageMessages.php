<?php

namespace App\Filament\Resources\MessagesResource\Pages;

use App\Filament\Resources\MessagesResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMessages extends ManageRecords
{
    protected static string $resource = MessagesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
