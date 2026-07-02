<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ClientWiseSalesWidget extends BaseWidget
{
    protected static ?string $heading = 'Client-wise Sales';

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->join('clients', 'clients.id', '=', 'orders.client_id')
                    ->whereIn('orders.status', ['confirmed', 'packed', 'dispatched', 'delivered'])
                    ->groupBy('orders.client_id', 'clients.company_name')
                    ->select('orders.client_id')
                    ->selectRaw('MIN(orders.id) as id')
                    ->selectRaw('clients.company_name as company_name')
                    ->selectRaw('COUNT(orders.id) as orders_count')
                    ->selectRaw('SUM(orders.total) as total_sales')
                    ->orderByDesc('total_sales')
            )
            ->columns([
                Tables\Columns\TextColumn::make('company_name')->label('Client'),
                Tables\Columns\TextColumn::make('orders_count')->label('Orders'),
                Tables\Columns\TextColumn::make('total_sales')->label('Total Sales')->money('INR'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
