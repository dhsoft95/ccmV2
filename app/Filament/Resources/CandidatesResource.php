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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;

class CandidatesResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'PEOPLE';

    protected static ?string $navigationLabel = 'Candidates';

    protected static ?string $modelLabel = 'Candidate';

    protected static ?string $pluralModelLabel = 'Candidates';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->description('Basic candidate details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('full_name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter candidate full name'),

                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('candidate@example.com'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('+255 XXX XXX XXX'),

                                Forms\Components\TextInput::make('sender_id')
                                    ->label('Sender ID')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Unique sender identifier'),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Political Information')
                    ->description('Political affiliation and position details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('party_affiliation')
                                    ->label('Party Affiliation')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Political party name'),

                                Forms\Components\Select::make('position_id')
                                    ->label('Position')
                                    ->relationship('position', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Forms\Components\Placeholder::make('supporters_info')
                            ->label('Supporters Information')
                            ->content(function ($record) {
                                if (!$record || !$record->exists) {
                                    return 'No supporters data available for new candidates';
                                }

                                $totalSupporters = $record->supporters()->count();
                                $promisedSupporters = $record->supporters()->where('promised', true)->count();

                                return "Total Supporters: {$totalSupporters} | Promised: {$promisedSupporters}";
                            }),
                    ])
                    ->columns(1),

                Section::make('Location Details')
                    ->description('Geographic information')
                    ->schema([
                        Forms\Components\Select::make('region_id')
                            ->label('Region')
                            ->relationship('region', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('district_id', null)),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('district_id')
                                    ->label('District')
                                    ->options(fn () => districts::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('ward_id', null)),

                                Forms\Components\Select::make('ward_id')
                                    ->label('Ward')
                                    ->options(fn () => ward::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('village_id', null)),

                                Forms\Components\Select::make('village_id')
                                    ->label('Village')
                                    ->options(fn () => village::all()->pluck('name', 'id'))
                                    ->searchable(),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Additional Information')
                    ->description('Other details and account settings')
                    ->schema([
                        Forms\Components\Textarea::make('other_candidate_details')
                            ->label('Additional Details')
                            ->rows(3)
                            ->placeholder('Any additional information about the candidate'),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('email_verified_at')
                                    ->label('Email Verified At')
                                    ->displayFormat('d/m/Y H:i'),

                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                    ->maxLength(255)
                                    ->placeholder('Secure password'),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('party_affiliation')
                    ->label('Party')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('position.name')
                    ->label('Position')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('supporters_count')
                    ->label('Total Supporters')
                    ->getStateUsing(fn ($record) => $record->supporters()->count())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('supporters')
                            ->orderBy('supporters_count', $direction);
                    })
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 1000 => 'success',
                        $state >= 500 => 'warning',
                        $state >= 100 => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => number_format($state)),

                Tables\Columns\TextColumn::make('promised_supporters_count')
                    ->label('Promised')
                    ->getStateUsing(fn ($record) => $record->supporters()->where('promised', true)->count())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount(['supporters as promised_supporters_count' => function ($query) {
                            $query->where('promised', true);
                        }])->orderBy('promised_supporters_count', $direction);
                    })
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (int $state): string => number_format($state))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('region.name')
                    ->label('Region')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('district.name')
                    ->label('District')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ward.name')
                    ->label('Ward')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('village.name')
                    ->label('Village')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->email_verified_at))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('position')
                    ->relationship('position', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('region')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('party_affiliation')
                    ->options(fn () => Candidate::distinct()->pluck('party_affiliation', 'party_affiliation')->toArray())
                    ->searchable(),

                Filter::make('supporters_range')
                    ->form([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('supporters_from')
                                    ->label('Total Supporters From')
                                    ->numeric()
                                    ->placeholder('Min supporters'),
                                Forms\Components\TextInput::make('supporters_to')
                                    ->label('Total Supporters To')
                                    ->numeric()
                                    ->placeholder('Max supporters'),
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['supporters_from'] ?? null,
                                fn (Builder $query, $value): Builder => $query->withCount('supporters')
                                    ->having('supporters_count', '>=', $value),
                            )
                            ->when(
                                $data['supporters_to'] ?? null,
                                fn (Builder $query, $value): Builder => $query->withCount('supporters')
                                    ->having('supporters_count', '<=', $value),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['supporters_from'] ?? null) {
                            $indicators['supporters_from'] = 'Supporters from: ' . $data['supporters_from'];
                        }
                        if ($data['supporters_to'] ?? null) {
                            $indicators['supporters_to'] = 'Supporters to: ' . $data['supporters_to'];
                        }
                        return $indicators;
                    }),

                Filter::make('promised_supporters')
                    ->form([
                        Forms\Components\TextInput::make('min_promised')
                            ->label('Minimum Promised Supporters')
                            ->numeric()
                            ->placeholder('Min promised supporters'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['min_promised'] ?? null,
                            fn (Builder $query, $value): Builder => $query->withCount(['supporters as promised_supporters_count' => function ($query) {
                                $query->where('promised', true);
                            }])->having('promised_supporters_count', '>=', $value)
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['min_promised'] ?? null) {
                            $indicators['min_promised'] = 'Min promised supporters: ' . $data['min_promised'];
                        }
                        return $indicators;
                    }),

                Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at'))
                    ->label('Email Verified'),
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
            ->defaultSort('supporters_count', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'supporters',
                'supporters as promised_supporters_count' => function ($query) {
                    $query->where('promised', true);
                }
            ]))
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCandidates::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['full_name', 'email', 'phone', 'party_affiliation'];
    }
}
