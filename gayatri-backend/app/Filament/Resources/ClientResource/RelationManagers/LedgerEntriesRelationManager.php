<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LedgerEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'ledgerEntries';

    protected static ?string $title = 'Ledger (Khata)';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, h:i A'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'invoice', 'debit_note' => 'danger',
                        'payment', 'credit_note' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ref_type')
                    ->label('Reference')
                    ->formatStateUsing(fn ($state, $record) => $state
                        ? class_basename($state) . ' #' . $record->ref_id
                        : '—'),
                Tables\Columns\TextColumn::make('amount_signed')
                    ->label('Amount')
                    ->money('INR')
                    ->color(fn ($state) => $state >= 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Running Balance')
                    ->money('INR'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
