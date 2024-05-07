<?php

namespace App\Filament\Resources\CandidatesResource\Pages;

use App\Filament\Resources\CandidatesResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCandidates extends ManageRecords
{
    protected static string $resource = CandidatesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
