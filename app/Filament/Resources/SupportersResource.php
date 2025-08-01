<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportersResource\Pages;
use App\Filament\Resources\SupportersResource\RelationManagers;
use App\Filament\Imports\SupportersImport;
use App\Models\Candidate;
use App\Models\districts;
use App\Models\Supporters;
use App\Models\village;
use App\Models\ward;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ImportAction;
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
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('dob'),
                Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])->required(),
                Forms\Components\Select::make('region_id')
                    ->relationship('region', 'name')
                    ->required(),
                Select::make('district_id')
                    ->label('District')
                    ->options(districts::all()->pluck('name', 'id'))
                    ->searchable(),
                Select::make('village_id')
                    ->label('Village')
                    ->options(village::all()->pluck('name', 'id'))
                    ->searchable(),
                Select::make('ward_id')
                    ->label('Ward')
                    ->options(ward::all()->pluck('name', 'id'))
                    ->searchable(),
                Select::make('candidate_id')
                    ->label('Candidate')
                    ->options(Candidate::all()->pluck('full_name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('promised')
                    ->default(false),
                Forms\Components\Textarea::make('other_supporter_details')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => Supporters::with(['region', 'district', 'ward', 'village', 'candidate']))
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dob')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ward.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->sortable()
                    ->searchable(),
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
                Tables\Filters\SelectFilter::make('candidate_id')
                    ->label('Candidate')
                    ->relationship('candidate', 'full_name'),
                Tables\Filters\SelectFilter::make('region_id')
                    ->label('Region')
                    ->relationship('region', 'name'),
                Tables\Filters\TernaryFilter::make('promised')
                    ->label('Promised Support'),
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
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(SupportersImport::class)
                    ->label('Import Supporters')
                    ->color('success'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSupporters::route('/'),
        ];
    }
}
