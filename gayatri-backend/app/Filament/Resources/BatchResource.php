<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchResource\Pages;
use App\Models\Batch;
use App\Services\StockService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Inventory';

    // Batches are only ever created via a Goods Receipt so origin stays traceable.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Batch identity')
                    ->description('Product and source are read-only — edit the GRN to correct those.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('batch_no')
                            ->label('Batch / Lot no.')
                            ->required(),
                        Forms\Components\DatePicker::make('expiry_date')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Pricing & condition')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Buy Price (₹)')
                            ->helperText(fn (?Batch $record) => $record?->movements()->where('type', 'out')->exists()
                                ? '⚠ Read-only — this batch has sales movements. Editing the price would corrupt historical cost records.'
                                : 'Cost per unit when this batch was purchased.')
                            ->disabled(fn (?Batch $record) => $record?->movements()->where('type', 'out')->exists() ?? false)
                            ->numeric()
                            ->prefix('₹')
                            ->required()
                            ->minValue(0),
                        Forms\Components\Select::make('condition')
                            ->options([
                                'good'        => 'Good — sellable',
                                'quarantine'  => 'Quarantine — hold',
                                'damaged'     => 'Damaged — write-off',
                            ])
                            ->required(),
                    ]),

                Forms\Components\Section::make('Stock quantities (read-only)')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('qty_received')
                            ->label('Qty received')
                            ->disabled(),
                        Forms\Components\TextInput::make('qty_remaining')
                            ->label('Qty remaining')
                            ->helperText('Use the "Adjust" action on the list to correct remaining qty.')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('batch_no')->label('Batch/Lot'),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Batch $record): string => match (true) {
                        now()->gte($record->expiry_date)                        => 'danger',
                        now()->diffInDays($record->expiry_date, false) < 90     => 'warning',
                        default                                                 => 'success',
                    }),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Buy Price')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty_remaining')
                    ->label('Remaining')
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty_received')
                    ->label('Received')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('batch_value')
                    ->label('Batch Value')
                    ->state(fn (Batch $record) => (float) $record->qty_remaining * (float) $record->purchase_price)
                    ->money('INR')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('condition')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'good'       => 'success',
                        'quarantine' => 'warning',
                        'damaged'    => 'danger',
                        default      => 'gray',
                    }),
            ])
            ->defaultSort('expiry_date')
            ->filters([
                Tables\Filters\SelectFilter::make('condition')->options([
                    'good'       => 'Good',
                    'quarantine' => 'Quarantine',
                    'damaged'    => 'Damaged',
                ]),
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->label('Product'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit price / condition'),
                Tables\Actions\Action::make('adjust')
                    ->label('Adjust qty')
                    ->icon('heroicon-o-wrench')
                    ->form([
                        Forms\Components\TextInput::make('actual_qty')
                            ->label('Actual quantity on shelf')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('reason')
                            ->required(),
                    ])
                    ->action(function (Batch $record, array $data) {
                        app(StockService::class)->adjustStock($record, (float) $data['actual_qty'], $data['reason'], auth()->id());
                        Notification::make()->title('Stock adjusted')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBatches::route('/'),
            'edit'  => Pages\EditBatch::route('/{record}/edit'),
        ];
    }
}
