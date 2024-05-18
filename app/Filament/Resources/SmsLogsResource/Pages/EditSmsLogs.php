<?php

namespace App\Filament\Resources\SmsLogsResource\Pages;

use App\Filament\Resources\SmsLogsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsLogs extends EditRecord
{
    protected static string $resource = SmsLogsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
