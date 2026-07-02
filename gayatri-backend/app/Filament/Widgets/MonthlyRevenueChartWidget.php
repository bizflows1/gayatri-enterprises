<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue — Last 6 Months';

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $revenues = [];
        $orderCounts = [];
        $now = now();

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $labels[] = $month->format('M Y');

            $row = Order::query()
                ->whereIn('status', ['confirmed', 'packed', 'dispatched', 'delivered'])
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as revenue')
                ->first();

            $revenues[] = (float) ($row->revenue ?? 0);
            $orderCounts[] = (int) ($row->count ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₹)',
                    'data' => $revenues,
                    'borderColor' => '#1B7A52',
                    'backgroundColor' => 'rgba(27, 122, 82, 0.12)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#1B7A52',
                    'pointRadius' => 5,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Orders',
                    'data' => $orderCounts,
                    'borderColor' => '#0F2C4A',
                    'backgroundColor' => 'rgba(15, 44, 74, 0.08)',
                    'fill' => false,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#0F2C4A',
                    'pointRadius' => 4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'position' => 'left',
                    'title' => ['display' => true, 'text' => 'Revenue (₹)'],
                ],
                'y1' => [
                    'type' => 'linear',
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                    'title' => ['display' => true, 'text' => 'Orders'],
                    'ticks' => ['stepSize' => 1],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'top'],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
