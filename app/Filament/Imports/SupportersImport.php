<?php

namespace App\Filament\Imports;

use App\Models\Supporters;
use App\Models\Candidate;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SupportersImport extends Importer
{
    protected static ?string $model = Supporters::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('first_name')
                ->label('First Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('last_name')
                ->label('Last Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('phone_number')
                ->label('Phone Number')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('+255712345678'),

            ImportColumn::make('candidate_id')
                ->label('Candidate ID')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'exists:candidates,id'])
                ->example('1'),
        ];
    }

    public function resolveRecord(): ?Supporters
    {
        return new Supporters();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your supporter import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
