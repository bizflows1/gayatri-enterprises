<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identity')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) =>
                                $context === 'create' ? $set('slug', Str::slug($state) . '-' . Str::lower(Str::random(5))) : null)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cas_number')
                            ->label('CAS number')
                            ->helperText('e.g. 67-64-1 — leave blank for blended/proprietary products.'),
                        Forms\Components\Select::make('brand_id')
                            ->label('Brand')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ]),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Pack & pricing')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('grade')
                            ->placeholder('AR Grade, LR Grade, HPLC Grade, ACS...'),
                        Forms\Components\TextInput::make('pack_size')
                            ->required()
                            ->placeholder('500ml, 2.5L, 25kg...')
                            ->helperText('One row per sellable pack size — a 500ml and a 25L of the same chemical are separate products with their own stock.'),
                        Forms\Components\TextInput::make('unit')
                            ->placeholder('ml, L, g, kg'),
                        Forms\Components\TextInput::make('hsn_code')
                            ->label('HSN code'),
                        Forms\Components\TextInput::make('sales_price')
                            ->numeric()
                            ->prefix('₹')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active / visible on site')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Documentation')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(4),
                        Forms\Components\FileUpload::make('sds_file')
                            ->label('SDS / MSDS (PDF)')
                            ->directory('sds')
                            ->acceptedFileTypes(['application/pdf']),
                        Forms\Components\FileUpload::make('coa_file')
                            ->label('Certificate of Analysis (PDF)')
                            ->directory('coa')
                            ->acceptedFileTypes(['application/pdf']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cas_number')->label('CAS')->searchable(),
                Tables\Columns\TextColumn::make('brand.name')->label('Brand')->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->sortable(),
                Tables\Columns\TextColumn::make('pack_size'),
                Tables\Columns\TextColumn::make('sales_price')->label('Sale Price')->money('INR'),
                Tables\Columns\TextColumn::make('avg_buy_price')
                    ->label('Avg. Buy Price')
                    ->state(function (Product $record): ?string {
                        $price = $record->avgPurchasePrice();
                        return $price !== null ? number_format($price, 2) : null;
                    })
                    ->prefix('₹')
                    ->placeholder('—')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('available_qty')
                    ->label('In Stock')
                    ->state(fn (Product $record) => $record->availableQty())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('inventory_value')
                    ->label('Stock Value')
                    ->state(fn (Product $record) => $record->inventoryValue())
                    ->money('INR')
                    ->color('primary')
                    ->sortable(false),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand_id')->relationship('brand', 'name')->label('Brand'),
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name')->label('Category'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
