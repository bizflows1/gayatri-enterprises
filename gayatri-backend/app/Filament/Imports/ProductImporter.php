<?php

namespace App\Filament\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('cas_number')
                ->label('CAS Number'),
            ImportColumn::make('brand')
                ->relationship(resolveUsing: fn (string $state) => Brand::firstOrCreate(['name' => trim($state)])),
            ImportColumn::make('category')
                ->relationship(resolveUsing: fn (string $state) => Category::firstOrCreate(['name' => trim($state)])),
            ImportColumn::make('hsn_code')
                ->label('HSN Code'),
            ImportColumn::make('grade'),
            ImportColumn::make('pack_size')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('unit'),
            ImportColumn::make('sales_price')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('description'),
            ImportColumn::make('is_active')
                ->boolean(),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $name = $this->data['name'] ?? null;
        $packSize = $this->data['pack_size'] ?? null;

        return Product::firstOrNew([
            'name' => $name,
            'pack_size' => $packSize,
        ]);
    }

    protected function beforeSave(): void
    {
        if (empty($this->record->slug)) {
            $this->record->slug = Str::slug($this->record->name . '-' . $this->record->pack_size) . '-' . Str::lower(Str::random(5));
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
