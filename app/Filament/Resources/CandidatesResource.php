<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidatesResource\Pages;
use App\Filament\Resources\CandidatesResource\RelationManagers;
use App\Models\Candidate;
use App\Models\districts;
use App\Models\User;
use App\Models\village;
use App\Models\ward;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class CandidatesResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';


    protected static ?string $navigationGroup = 'PEOPLE';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('party_affiliation')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('position_id')
                    ->relationship('position', 'name')
                    ->required(),
                Forms\Components\Select::make('region_id')
                    ->relationship('region', 'name')
                    ->required(),

                Select::make('village_id')
                    ->label('Village')
                    ->options(village::all()->pluck('name', 'id'))
                    ->searchable(),

                Select::make('ward_id')
                    ->label('Ward')
                    ->options(ward::all()->pluck('name', 'id'))
                    ->searchable(),
                Select::make('district_id')
                    ->label('District')
                    ->options(districts::all()->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Textarea::make('other_candidate_details')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                Tables\Columns\TextColumn::make('user.name')
//                    ->numeric()
//                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('party_affiliation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ward.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ManageCandidates::route('/'),
        ];
    }
}
