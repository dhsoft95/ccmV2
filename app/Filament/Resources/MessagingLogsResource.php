<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessagingLogsResource\Pages;
use App\Filament\Resources\MessagingLogsResource\RelationManagers;
use App\Models\messaging_logs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Novadaemon\FilamentPrettyJson\PrettyJson;

class MessagingLogsResource extends Resource
{
    protected static ?string $model = messaging_logs::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                PrettyJson::make('response'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supporter.full_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('channel')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('success')
                    ->searchable(),
                Tables\Columns\IconColumn::make('success')
                    ->boolean(),
                Tables\Columns\TextColumn::make('response')
                    ->sortable()->alignJustify()->limit(20),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMessagingLogs::route('/'),
        ];
    }
}
