<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use App\Models\Client;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    private const SALE_STATUSES = ['confirmed', 'packed', 'dispatched', 'delivered'];

    protected function getStats(): array
    {
        $totalSales = Order::whereIn('status', self::SALE_STATUSES)->sum('total');
        $totalOrders = Order::whereIn('status', self::SALE_STATUSES)->count();

        $ordersThisMonth = Order::whereIn('status', self::SALE_STATUSES)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $salesThisMonth = Order::whereIn('status', self::SALE_STATUSES)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $totalOutstanding = Client::sum('outstanding_balance');

        $inventoryValue = Batch::where('condition', 'good')
            ->where('expiry_date', '>', now())
            ->selectRaw('SUM(qty_remaining * purchase_price) as value')
            ->value('value') ?? 0;

        $inventoryItems = Batch::where('condition', 'good')
            ->where('expiry_date', '>', now())
            ->where('qty_remaining', '>', 0)
            ->count();

        return [
            Stat::make('Total Sales', '₹' . number_format((float) $totalSales, 2))
                ->description('All confirmed orders')
                ->color('success'),
            Stat::make('Sales This Month', '₹' . number_format((float) $salesThisMonth, 2))
                ->description($ordersThisMonth . ' orders this month')
                ->color('primary'),
            Stat::make('Inventory Value', '₹' . number_format((float) $inventoryValue, 2))
                ->description($inventoryItems . ' active batches in stock')
                ->color('warning'),
            Stat::make('Total Outstanding', '₹' . number_format((float) $totalOutstanding, 2))
                ->description('Across all clients')
                ->color($totalOutstanding > 0 ? 'danger' : 'success'),
        ];
    }
}
