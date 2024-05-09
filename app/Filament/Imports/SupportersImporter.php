<?php

namespace App\Filament\Imports;

use App\Models\Supporters;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SupportersImporter extends Importer
{
    protected static ?string $model = Supporters::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('first_name')
                ->rules(['max:255']),
            ImportColumn::make('last_name')
                ->rules(['max:255']),
            ImportColumn::make('dob')
                ->rules(['date']),
            ImportColumn::make('gander')
                ->rules(['max:255']),
            ImportColumn::make('region')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('village')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('ward')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('district_id')
                ->requiredMapping()->label('District')
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('phone_number')
                ->rules(['max:255']),
            ImportColumn::make('promised')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
            ImportColumn::make('other_supporter_details'),
        ];
    }

    public function resolveRecord(): ?Supporters
    {
        // return Supporters::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Supporters();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your supporters import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
