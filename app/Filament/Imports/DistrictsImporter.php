<?php

namespace App\Filament\Imports;

use App\Models\Districts;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class DistrictsImporter extends Importer
{
    protected static ?string $model = Districts::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('region_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('other_district_details'),
        ];
    }

    public function resolveRecord(): ?Districts
    {
        // return Districts::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Districts();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your districts import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
