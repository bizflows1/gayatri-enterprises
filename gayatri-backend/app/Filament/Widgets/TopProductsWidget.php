<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Products';

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->whereIn('orders.status', ['confirmed', 'packed', 'dispatched', 'delivered'])
                    ->groupBy('order_items.product_id', 'products.name', 'products.cas_number')
                    ->select('order_items.product_id')
                    ->selectRaw('MIN(order_items.id) as id')
                    ->selectRaw('products.name as product_name')
                    ->selectRaw('products.cas_number as cas_number')
                    ->selectRaw('SUM(order_items.qty) as total_qty')
                    ->selectRaw('SUM(order_items.qty * COALESCE(order_items.negotiated_price, order_items.unit_price, 0)) as total_revenue')
                    ->orderByDesc('total_revenue')
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')->label('Product'),
                Tables\Columns\TextColumn::make('cas_number')->label('CAS No.')->placeholder('—'),
                Tables\Columns\TextColumn::make('total_qty')->label('Qty Sold')->numeric(2),
                Tables\Columns\TextColumn::make('total_revenue')->label('Revenue')->money('INR'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
