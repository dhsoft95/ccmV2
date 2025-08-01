<?php

namespace App\Filament\Widgets;

use App\Models\sms_logs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmsMessagesChart extends ChartWidget
{
    protected static ?string $heading = 'SMS Messages Overview';

    protected static ?string $description = 'Daily SMS delivery statistics';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '30s';

    public ?string $filter = 'last7days';

    protected function getData(): array
    {
        $period = match ($this->filter) {
            'today' => 1,
            'last7days' => 7,
            'last30days' => 30,
            'last90days' => 90,
            default => 30,
        };

        $data = $this->getMessagesData($period);

        return [
            'datasets' => [
                [
                    'label' => 'Delivered',
                    'data' => $data['delivered'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Failed',
                    'data' => $data['failed'],
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Pending',
                    'data' => $data['pending'],
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'last7days' => 'Last 7 days',
            'last30days' => 'Last 30 days',
            'last90days' => 'Last 90 days',
        ];
    }

    private function getMessagesData(int $days): array
    {
        $delivered = [];
        $failed = [];
        $pending = [];
        $labels = [];

        try {
            if ($days === 1) {
                // Hourly data for today - using time ranges instead of whereHour
                for ($i = 0; $i < 24; $i++) {
                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                    $labels[] = $hour;

                    // Create time range for this hour
                    $startTime = Carbon::today()->addHours($i);
                    $endTime = Carbon::today()->addHours($i + 1);

                    // Use DB::table to avoid any model casting issues
                    $deliveredCount = DB::table('sms_logs')
                        ->whereBetween('created_at', [$startTime, $endTime])
                        ->where('status', '1')
                        ->count();

                    $failedCount = DB::table('sms_logs')
                        ->whereBetween('created_at', [$startTime, $endTime])
                        ->where('status', '0')
                        ->count();

                    $pendingCount = DB::table('sms_logs')
                        ->whereBetween('created_at', [$startTime, $endTime])
                        ->whereIn('status', ['2', '3', '4'])
                        ->count();

                    $delivered[] = $deliveredCount;
                    $failed[] = $failedCount;
                    $pending[] = $pendingCount;
                }
            } else {
                // Daily data
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $labels[] = $date->format('M j');

                    // Use DB::table for consistency
                    $deliveredCount = DB::table('sms_logs')
                        ->whereDate('created_at', $date->toDateString())
                        ->where('status', '1')
                        ->count();

                    $failedCount = DB::table('sms_logs')
                        ->whereDate('created_at', $date->toDateString())
                        ->where('status', '0')
                        ->count();

                    $pendingCount = DB::table('sms_logs')
                        ->whereDate('created_at', $date->toDateString())
                        ->whereIn('status', ['2', '3', '4'])
                        ->count();

                    $delivered[] = $deliveredCount;
                    $failed[] = $failedCount;
                    $pending[] = $pendingCount;
                }
            }
        } catch (\Exception $e) {
            // Fallback data in case of errors
            \Log::error('SmsMessagesChart error: ' . $e->getMessage());

            $delivered = array_fill(0, $days === 1 ? 24 : $days, 0);
            $failed = array_fill(0, $days === 1 ? 24 : $days, 0);
            $pending = array_fill(0, $days === 1 ? 24 : $days, 0);

            if ($days === 1) {
                $labels = array_map(fn($i) => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00', range(0, 23));
            } else {
                $labels = array_map(fn($i) => Carbon::now()->subDays($days - 1 - $i)->format('M j'), range(0, $days - 1));
            }
        }

        return [
            'delivered' => $delivered,
            'failed' => $failed,
            'pending' => $pending,
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
