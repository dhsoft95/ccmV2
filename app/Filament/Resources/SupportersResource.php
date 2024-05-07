<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportersResource\Pages;
use App\Filament\Resources\SupportersResource\RelationManagers;
use App\Models\candidates;
use App\Models\regions;
use App\Models\Supporters;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupportersResource extends Resource
{
    protected static ?string $model = Supporters::class;

    protected static ?string $navigationGroup = 'PEOPLE';


    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Region')
                    ->options(User::all()->pluck('name', 'id'))
                    ->searchable()->label('Created By'),
                Forms\Components\TextInput::make('full_name')
                    ->maxLength(255)
                    ->default(null)->required(),
                Forms\Components\DatePicker::make('dob')->label('Date of birth'),
                Select::make('gander')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])->required(),
                Select::make('candidate_id')
                    ->label('Candidate')
                    ->options(candidates::all()->pluck('full_name', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('promised')
                    ->required(),
                Forms\Components\Textarea::make('other_supporter_details')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dob')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gander')
                    ->searchable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\IconColumn::make('promised')
                    ->boolean(),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageSupporters::route('/'),
        ];
    }
}
