<?php

namespace App\Filament\Widgets;

use App\Models\sms_logs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentSmsMessages extends BaseWidget
{
    protected static ?string $heading = 'Recent SMS Messages';

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '15s';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                sms_logs::query()
                    ->with(['candidate'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Candidate')
                    ->weight('bold')
                    ->icon('heroicon-m-user')
                    ->iconColor('primary')
                    ->placeholder('Unknown'),

                Tables\Columns\TextColumn::make('recipient')
                    ->label('Recipient')
                    ->icon('heroicon-m-phone')
                    ->iconColor('gray'),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(60)
                    ->tooltip(function ($record): ?string {
                        return strlen($record->message) > 60 ? $record->message : null;
                    })
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('l, F j, Y \a\t g:i A'))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->url(fn ($record) => route('filament.admin.resources.sms-logs.view', ['record' => $record->id])),

                Tables\Actions\Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === '0')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Add your SMS resend logic here
                        $record->update(['status' => '2']);
                    }),
            ])
            ->emptyStateHeading('No SMS messages yet')
            ->emptyStateDescription('SMS messages will appear here once they are sent.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->paginated(false);
    }
}
