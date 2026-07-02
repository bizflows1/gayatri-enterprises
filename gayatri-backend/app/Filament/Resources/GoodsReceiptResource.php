<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoodsReceiptResource\Pages;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\StockService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GoodsReceiptResource extends Resource
{
    protected static ?string $model = GoodsReceipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Goods Receipts (GRN)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('po_id')
                    ->label('Purchase order (optional)')
                    ->options(fn () => PurchaseOrder::query()->pluck('id', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('received_at')
                    ->required()
                    ->default(now()),
                Forms\Components\Hidden::make('received_by')
                    ->default(fn () => auth()->id()),

                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(fn () => Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('batch_no')
                            ->label('Batch / lot no.')
                            ->required(),
                        Forms\Components\DatePicker::make('expiry_date')
                            ->required(),
                        Forms\Components\TextInput::make('qty')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('purchase_price')
                            ->numeric()
                            ->prefix('₹')
                            ->required(),
                    ])
                    ->addActionLabel('Add line item')
                    ->helperText('Saving here records the receipt. Use "Receive into stock" on the list afterwards to actually create batches and make this stock sellable.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('GRN #')->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('po_id')->label('PO #'),
                Tables\Columns\TextColumn::make('received_at')->dateTime('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('items_count')->label('Line items')->counts('items'),
                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Stock status')
                    ->state(fn (GoodsReceipt $record) => $record->items()->whereNull('batch_id')->exists() ? 'Pending' : 'Received into stock')
                    ->badge()
                    ->color(fn ($state) => $state === 'Pending' ? 'warning' : 'success'),
            ])
            ->actions([
                Tables\Actions\Action::make('receive')
                    ->label('Receive into stock')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->visible(fn (GoodsReceipt $record) => $record->items()->whereNull('batch_id')->exists())
                    ->requiresConfirmation()
                    ->modalDescription('This creates a batch per line item and makes the quantities sellable immediately. This cannot be undone from here — use stock adjustment afterwards to correct mistakes.')
                    ->action(function (GoodsReceipt $record) {
                        app(StockService::class)->receiveGoodsReceipt($record, auth()->id());
                        Notification::make()
                            ->title('Stock received')
                            ->body("Batches created for GRN #{$record->id}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoodsReceipts::route('/'),
            'create' => Pages\CreateGoodsReceipt::route('/create'),
            'edit' => Pages\EditGoodsReceipt::route('/{record}/edit'),
        ];
    }
}
