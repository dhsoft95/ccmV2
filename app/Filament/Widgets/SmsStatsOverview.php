<?php

namespace App\Filament\Widgets;

use App\Models\sms_logs;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SmsStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // Get SMS statistics
        $totalMessages = sms_logs::count();
        $deliveredMessages = sms_logs::where('status', '1')->count();
        $failedMessages = sms_logs::where('status', '0')->count();
        $pendingMessages = sms_logs::whereIn('status', ['2', '3', '4'])->count();

        // Calculate percentages
        $deliveryRate = $totalMessages > 0 ? round(($deliveredMessages / $totalMessages) * 100, 1) : 0;
        $failureRate = $totalMessages > 0 ? round(($failedMessages / $totalMessages) * 100, 1) : 0;

        // Get recent trends (last 7 days vs previous 7 days)
        $recentDelivered = sms_logs::where('status', '1')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $previousDelivered = sms_logs::where('status', '1')
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->count();

        $recentFailed = sms_logs::where('status', '0')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $previousFailed = sms_logs::where('status', '0')
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->count();

        // Calculate trends
        $deliveredTrend = $previousDelivered > 0
            ? round((($recentDelivered - $previousDelivered) / $previousDelivered) * 100, 1)
            : ($recentDelivered > 0 ? 100 : 0);

        $failedTrend = $previousFailed > 0
            ? round((($recentFailed - $previousFailed) / $previousFailed) * 100, 1)
            : ($recentFailed > 0 ? 100 : 0);

        return [
            Stat::make('Total Messages', number_format($totalMessages))
                ->description('All SMS messages sent')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('primary')
                ->chart($this->getLastSevenDaysData('total')),

            Stat::make('Delivered Messages', number_format($deliveredMessages))
                ->description($deliveredTrend >= 0 ? "{$deliveredTrend}% increase" : "{$deliveredTrend}% decrease")
                ->descriptionIcon($deliveredTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($this->getLastSevenDaysData('delivered'))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.sms-logs.index', ['tableFilters' => ['status' => ['values' => ['1']]]])),

            Stat::make('Failed Messages', number_format($failedMessages))
                ->description($failedTrend >= 0 ? "{$failedTrend}% increase" : "{$failedTrend}% decrease")
                ->descriptionIcon($failedTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart($this->getLastSevenDaysData('failed'))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.sms-logs.index', ['tableFilters' => ['status' => ['values' => ['0']]]])),

            Stat::make('Pending Messages', number_format($pendingMessages))
                ->description('Queued, Sent & Processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart($this->getLastSevenDaysData('pending'))
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ])
                ->url(route('filament.admin.resources.sms-logs.index', ['tableFilters' => ['status' => ['values' => ['2', '3', '4']]]])),

            Stat::make('Delivery Rate', "{$deliveryRate}%")
                ->description('Overall success rate')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($deliveryRate >= 90 ? 'success' : ($deliveryRate >= 70 ? 'warning' : 'danger')),

            Stat::make('Today\'s Messages', number_format(sms_logs::whereDate('created_at', today())->count()))
                ->description('Messages sent today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->chart($this->getTodayHourlyData()),
        ];
    }

    private function getLastSevenDaysData(string $type): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();

            $query = sms_logs::whereDate('created_at', $date);

            $count = match ($type) {
                'total' => $query->count(),
                'delivered' => $query->where('status', '1')->count(),
                'failed' => $query->where('status', '0')->count(),
                'pending' => $query->whereIn('status', ['2', '3', '4'])->count(),
                default => 0,
            };

            $data[] = $count;
        }

        return $data;
    }

    private function getTodayHourlyData(): array
    {
        $data = [];
        $currentHour = now()->hour;

        for ($i = 0; $i <= $currentHour; $i++) {
            $count = sms_logs::whereDate('created_at', today())
                ->whereRaw('HOUR(created_at) = ?', [$i])
                ->count();
            $data[] = $count;
        }

        return $data;
    }
}
