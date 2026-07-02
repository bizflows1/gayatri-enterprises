<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OutstandingPaymentsWidget extends BaseWidget
{
    protected static ?string $heading = 'Outstanding Payments';

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Client::query()
                    ->where('outstanding_balance', '>', 0)
                    ->orderByDesc('outstanding_balance')
            )
            ->columns([
                Tables\Columns\TextColumn::make('company_name')->label('Client'),
                Tables\Columns\TextColumn::make('credit_limit')->money('INR'),
                Tables\Columns\TextColumn::make('outstanding_balance')->label('Outstanding')->money('INR')->color('danger'),
                Tables\Columns\TextColumn::make('assignedStaff.name')->label('Assigned Staff')->placeholder('Unassigned'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
