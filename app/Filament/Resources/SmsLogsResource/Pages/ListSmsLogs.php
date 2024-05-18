<?php

namespace App\Filament\Resources\SmsLogsResource\Pages;

use App\Filament\Resources\SmsLogsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmsLogs extends ListRecords
{
    protected static string $resource = SmsLogsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
