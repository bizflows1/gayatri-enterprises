<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StaffPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Staff Performance';

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Client::query()
                    ->join('users', 'users.id', '=', 'clients.assigned_staff_id')
                    ->leftJoin('orders', function ($join) {
                        $join->on('orders.client_id', '=', 'clients.id')
                            ->whereIn('orders.status', ['confirmed', 'packed', 'dispatched', 'delivered']);
                    })
                    ->groupBy('clients.assigned_staff_id', 'users.name')
                    ->select('clients.assigned_staff_id')
                    ->selectRaw('MIN(clients.id) as id')
                    ->selectRaw('users.name as staff_name')
                    ->selectRaw('COUNT(DISTINCT clients.id) as clients_count')
                    ->selectRaw('COUNT(orders.id) as orders_count')
                    ->selectRaw('COALESCE(SUM(orders.total), 0) as total_sales')
                    ->orderByDesc('total_sales')
            )
            ->columns([
                Tables\Columns\TextColumn::make('staff_name')->label('Staff'),
                Tables\Columns\TextColumn::make('clients_count')->label('Clients Assigned'),
                Tables\Columns\TextColumn::make('orders_count')->label('Orders Handled'),
                Tables\Columns\TextColumn::make('total_sales')->label('Total Sales')->money('INR'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
