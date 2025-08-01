<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsLogsResource\Pages;
use App\Filament\Resources\SmsLogsResource\RelationManagers;
use App\Models\sms_logs;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Carbon\Carbon;

class SmsLogsResource extends Resource
{
    protected static ?string $model = sms_logs::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'COMMUNICATIONS';

    protected static ?string $navigationLabel = 'SMS Logs';

    protected static ?string $modelLabel = 'SMS Log';

    protected static ?string $pluralModelLabel = 'SMS Logs';

    protected static ?int $navigationSort = 1;

    // No form needed - logs are view-only
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Candidate')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->icon('heroicon-m-user')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('recipient')
                    ->label('Recipient')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-phone')
                    ->iconColor('gray')
                    ->copyable()
                    ->copyMessage('Phone number copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function ($record): ?string {
                        return strlen($record->message) > 50 ? $record->message : null;
                    })
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Delivery Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => 'Failed',
                        '1' => 'Delivered',
                        '2' => 'Pending',
                        '3' => 'Sent',
                        '4' => 'Queued',
                        default => 'Unknown',
                    })
                    ->colors([
                        'danger' => '0',
                        'success' => '1',
                        'warning' => ['2', '3'],
                        'info' => '4',
                        'gray' => 'default',
                    ])
                    ->icons([
                        'heroicon-m-x-circle' => '0',
                        'heroicon-m-check-circle' => '1',
                        'heroicon-m-clock' => ['2', '3'],
                        'heroicon-m-queue-list' => '4',
                    ]),

                Tables\Columns\TextColumn::make('message_length')
                    ->label('Length')
                    ->getStateUsing(fn ($record) => strlen($record->message))
                    ->suffix(' chars')
                    ->alignCenter()
                    ->color(fn (int $state): string => match (true) {
                        $state > 160 => 'danger',
                        $state > 140 => 'warning',
                        default => 'success',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent At')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('l, F j, Y \a\t g:i A')),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_recent')
                    ->label('Recent')
                    ->getStateUsing(fn ($record) => $record->created_at->isAfter(now()->subHours(24)))
                    ->boolean()
                    ->trueIcon('heroicon-o-clock')
                    ->falseIcon('heroicon-o-calendar-days')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Delivery Status')
                    ->options([
                        '0' => 'Failed',
                        '1' => 'Delivered',
                        '2' => 'Pending',
                        '3' => 'Sent',
                        '4' => 'Queued',
                    ])
                    ->multiple(),

                SelectFilter::make('candidate')
                    ->relationship('candidate', 'full_name')
                    ->searchable()
                    ->preload(),

                Filter::make('recent')
                    ->label('Recent Messages (24h)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDay()))
                    ->toggle(),

                Filter::make('failed_messages')
                    ->label('Failed Messages Only')
                    ->query(fn (Builder $query): Builder => $query->where('status', '0'))
                    ->toggle(),

                Filter::make('long_messages')
                    ->label('Long Messages (>160 chars)')
                    ->query(function (Builder $query): Builder {
                        return $query->whereRaw('LENGTH(message) > 160');
                    })
                    ->toggle(),

                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('sent_from')
                            ->label('Sent From'),
                        Forms\Components\DatePicker::make('sent_until')
                            ->label('Sent Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sent_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['sent_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['sent_from'] ?? null) {
                            $indicators['sent_from'] = 'Sent from: ' . Carbon::parse($data['sent_from'])->toFormattedDateString();
                        }
                        if ($data['sent_until'] ?? null) {
                            $indicators['sent_until'] = 'Sent until: ' . Carbon::parse($data['sent_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('SMS Log Details')
                    ->modalContent(function ($record) {
                        return view('filament.sms-log-details', ['record' => $record]);
                    }),

                Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === '0') // Only show for failed messages
                    ->requiresConfirmation()
                    ->modalHeading('Resend SMS')
                    ->modalDescription('Are you sure you want to resend this SMS message?')
                    ->action(function ($record) {
                        // Add your SMS resend logic here
                        // Example: dispatch a job to resend the SMS
                        // ResendSmsJob::dispatch($record);

                        $record->update(['status' => '2']); // Set to pending

                        return redirect()->back()->with('success', 'SMS queued for resending');
                    }),

                Action::make('copy_message')
                    ->label('Copy Message')
                    ->icon('heroicon-o-clipboard')
                    ->color('gray')
                    ->action(function ($record) {
                        // This will copy to clipboard using Alpine.js
                        return $record->message;
                    })
                    ->modalContent(fn ($record) => view('filament.copy-message-modal', ['message' => $record->message])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('mark_as_delivered')
                        ->label('Mark as Delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update(['status' => '1']));
                        }),

                    BulkAction::make('mark_as_failed')
                        ->label('Mark as Failed')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn ($record) => $record->update(['status' => '0']));
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete SMS Logs')
                        ->modalDescription('Are you sure you want to delete these SMS logs? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([25, 50, 100])
            ->poll('30s') // Auto-refresh every 30 seconds for real-time updates
            ->emptyStateHeading('No SMS logs found')
            ->emptyStateDescription('SMS logs will appear here once messages are sent.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
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
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', '0')->count() ?: null; // Show failed message count
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $failedCount = static::getModel()::where('status', '0')->count();
        return $failedCount > 0 ? 'danger' : null;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['recipient', 'message', 'candidate.full_name'];
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return "SMS to {$record->recipient}";
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Candidate' => $record->candidate?->full_name ?? 'Unknown',
            'Status' => match($record->status) {
                '0' => 'Failed',
                '1' => 'Delivered',
                '2' => 'Pending',
                '3' => 'Sent',
                '4' => 'Queued',
                default => 'Unknown',
            },
            'Sent' => $record->created_at->diffForHumans(),
        ];
    }
}
