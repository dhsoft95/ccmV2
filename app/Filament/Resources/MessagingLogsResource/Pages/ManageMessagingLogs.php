<?php

namespace App\Filament\Resources\MessagingLogsResource\Pages;

use App\Filament\Resources\MessagingLogsResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMessagingLogs extends ManageRecords
{
    protected static string $resource = MessagingLogsResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
