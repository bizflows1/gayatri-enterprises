<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ClientWiseSalesWidget;
use App\Filament\Widgets\OutstandingPaymentsWidget;
use App\Filament\Widgets\SalesOverviewWidget;
use App\Filament\Widgets\StaffPerformanceWidget;
use App\Filament\Widgets\TopProductsWidget;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Sales';

    protected static string $view = 'filament.pages.reports';

    protected function getHeaderWidgets(): array
    {
        return [
            SalesOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TopProductsWidget::class,
            ClientWiseSalesWidget::class,
            StaffPerformanceWidget::class,
            OutstandingPaymentsWidget::class,
        ];
    }
}
