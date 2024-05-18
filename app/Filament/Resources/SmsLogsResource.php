<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsLogsResource\Pages;
use App\Filament\Resources\SmsLogsResource\RelationManagers;
use App\Models\sms_logs;
use App\Models\SmsLogs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsLogsResource extends Resource
{
    protected static ?string $model = sms_logs::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recipient')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recipient')
                    ->searchable(),
                Tables\Columns\TextColumn::make('message')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),

                IconColumn::make('status')
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'danger',
                        '1' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsLogs::route('/'),
            'create' => Pages\CreateSmsLogs::route('/create'),
            'edit' => Pages\EditSmsLogs::route('/{record}/edit'),
        ];
    }
}
