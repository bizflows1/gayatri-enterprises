<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Details')->columns(2)->schema([
                Forms\Components\TextInput::make('id')->label('Order #')->disabled(),
                Forms\Components\TextInput::make('client.company_name')->label('Client')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
                Forms\Components\TextInput::make('payment_status')->label('Payment Status')->disabled(),
                Forms\Components\TextInput::make('payment_mode')->label('Payment Mode')->disabled(),
                Forms\Components\TextInput::make('total')->prefix('₹')->disabled(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft'      => 'gray',
                        'confirmed'  => 'info',
                        'packed'     => 'primary',
                        'dispatched' => 'warning',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state) => match ($state) { 'paid' => 'success', 'unpaid' => 'warning', default => 'gray' }),
                Tables\Columns\TextColumn::make('payment_mode')
                    ->label('Mode')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'cash'   => 'Cash',
                        'cheque' => 'Cheque',
                        'neft'   => 'NEFT',
                        default  => '—',
                    }),
                Tables\Columns\TextColumn::make('invoice.invoice_no')
                    ->label('Invoice')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'      => 'Draft',
                        'confirmed'  => 'Confirmed',
                        'packed'     => 'Packed',
                        'dispatched' => 'Dispatched',
                        'delivered'  => 'Delivered',
                        'cancelled'  => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options(['paid' => 'Paid', 'unpaid' => 'Unpaid']),
            ])
            ->actions([
                // Status progression actions
                Tables\Actions\Action::make('mark_packed')
                    ->label('Mark Packed')
                    ->icon('heroicon-o-archive-box')
                    ->color('primary')
                    ->visible(fn (Order $r) => $r->status === 'confirmed')
                    ->requiresConfirmation()
                    ->action(fn (Order $r) => self::updateStatus($r, 'packed')),

                Tables\Actions\Action::make('mark_dispatched')
                    ->label('Mark Dispatched')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->visible(fn (Order $r) => $r->status === 'packed')
                    ->form([
                        Forms\Components\Textarea::make('dispatch_note')
                            ->label('Dispatch Note (optional)')
                            ->placeholder('Courier name, tracking number, estimated delivery date...')
                            ->rows(2),
                    ])
                    ->action(fn (Order $r, array $data) => self::updateStatus($r, 'dispatched', $data['dispatch_note'] ?? null)),

                Tables\Actions\Action::make('mark_delivered')
                    ->label('Mark Delivered')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Order $r) => $r->status === 'dispatched')
                    ->requiresConfirmation()
                    ->action(fn (Order $r) => self::updateStatus($r, 'delivered')),

                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Order $r) => ($r->payment_status ?? 'unpaid') === 'unpaid' && $r->status !== 'cancelled')
                    ->requiresConfirmation()
                    ->modalDescription('Mark this order as fully paid. This does not create a ledger entry — use the Client → Record Payment action for that.')
                    ->action(function (Order $r) {
                        $r->update(['payment_status' => 'paid']);
                        Notification::make()->title('Order marked as paid.')->success()->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $r) => in_array($r->status, ['draft', 'confirmed', 'packed']))
                    ->requiresConfirmation()
                    ->modalHeading('Cancel this order?')
                    ->modalDescription('Stock reservations will NOT be automatically released — go to Batches and adjust manually if needed.')
                    ->action(fn (Order $r) => self::updateStatus($r, 'cancelled')),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    protected static function updateStatus(Order $order, string $status, ?string $note = null): void
    {
        $order->update(['status' => $status]);

        // Email the client
        $clientUser = $order->client?->user;
        if ($clientUser?->email) {
            try {
                Mail::to($clientUser->email)
                    ->send(new OrderStatusUpdatedMail($order->load(['client', 'items.product', 'invoice']), $status));
            } catch (\Throwable) {
                // Mail failure should never block the status update
            }
        }

        $labels = [
            'packed'     => 'packed',
            'dispatched' => 'dispatched — client notified by email',
            'delivered'  => 'delivered — client notified by email',
            'cancelled'  => 'cancelled',
        ];

        Notification::make()
            ->title('Order #' . $order->id . ' marked as ' . ($labels[$status] ?? $status) . '.')
            ->success()
            ->send();
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
