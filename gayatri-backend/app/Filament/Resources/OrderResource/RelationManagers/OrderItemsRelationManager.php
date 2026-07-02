<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Order Items';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Product'),
                Tables\Columns\TextColumn::make('product.cas_number')->label('CAS No.')->placeholder('—'),
                Tables\Columns\TextColumn::make('product.pack_size')->label('Pack Size'),
                Tables\Columns\TextColumn::make('qty')->label('Qty'),
                Tables\Columns\TextColumn::make('unit_price')->label('Unit Price')->money('INR'),
                Tables\Columns\TextColumn::make('negotiated_price')->label('Negotiated Price')->money('INR')->placeholder('—'),
            ]);
    }
}
