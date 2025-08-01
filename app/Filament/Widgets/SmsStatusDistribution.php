<?php

namespace App\Filament\Widgets;

use App\Models\sms_logs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SmsStatusDistribution extends ChartWidget
{
    protected static ?string $heading = 'Message Status Distribution';

    protected static ?string $description = 'Breakdown of SMS delivery statuses';

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        try {
            // Use DB::table() to avoid any model issues
            $statusCounts = DB::table('sms_logs')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Initialize counts with defaults
            $delivered = $statusCounts['1'] ?? 0;
            $failed = $statusCounts['0'] ?? 0;
            $pending = $statusCounts['2'] ?? 0;
            $sent = $statusCounts['3'] ?? 0;
            $queued = $statusCounts['4'] ?? 0;

            // Filter out empty values to avoid empty chart segments
            $data = [];
            $labels = [];
            $colors = [];

            if ($delivered > 0) {
                $data[] = $delivered;
                $labels[] = 'Delivered';
                $colors[] = 'rgb(34, 197, 94)';
            }

            if ($failed > 0) {
                $data[] = $failed;
                $labels[] = 'Failed';
                $colors[] = 'rgb(239, 68, 68)';
            }

            if ($pending > 0) {
                $data[] = $pending;
                $labels[] = 'Pending';
                $colors[] = 'rgb(245, 158, 11)';
            }

            if ($sent > 0) {
                $data[] = $sent;
                $labels[] = 'Sent';
                $colors[] = 'rgb(59, 130, 246)';
            }

            if ($queued > 0) {
                $data[] = $queued;
                $labels[] = 'Queued';
                $colors[] = 'rgb(168, 85, 247)';
            }

            // If no data at all, show a placeholder
            if (empty($data)) {
                $data = [1];
                $labels = ['No Data'];
                $colors = ['rgb(156, 163, 175)'];
            }

            return [
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => $colors,
                        'borderWidth' => 2,
                        'borderColor' => 'rgb(255, 255, 255)',
                        'hoverBorderWidth' => 3,
                        'hoverBorderColor' => 'rgb(255, 255, 255)',
                    ],
                ],
                'labels' => $labels,
            ];

        } catch (\Exception $e) {
            // Fallback in case of any database errors
            \Log::error('SmsStatusDistribution widget error: ' . $e->getMessage());

            return [
                'datasets' => [
                    [
                        'data' => [1],
                        'backgroundColor' => ['rgb(156, 163, 175)'],
                        'borderWidth' => 2,
                        'borderColor' => 'rgb(255, 255, 255)',
                    ],
                ],
                'labels' => ['Error Loading Data'],
            ];
        }
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => 'rgb(255, 255, 255)',
                    'bodyColor' => 'rgb(255, 255, 255)',
                    'borderColor' => 'rgba(255, 255, 255, 0.1)',
                    'borderWidth' => 1,
                    'callbacks' => [
                        'label' => 'function(context) {
                            if (context.label === "No Data" || context.label === "Error Loading Data") {
                                return context.label;
                            }
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ": " + context.parsed.toLocaleString() + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'cutout' => '60%',
            'animation' => [
                'animateRotate' => true,
                'animateScale' => false,
            ],
            'elements' => [
                'arc' => [
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    // Add a method to get the heading with total count
    public function getHeading(): ?string
    {
        try {
            $total = sms_logs::count();
            return static::$heading . " ({$total} total)";
        } catch (\Exception $e) {
            return static::$heading;
        }
    }
}
