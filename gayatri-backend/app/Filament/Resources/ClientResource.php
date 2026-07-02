<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers\LedgerEntriesRelationManager;
use App\Models\Client;
use App\Models\Payment;
use App\Models\User;
use App\Mail\PaymentReceivedMail;
use App\Services\LedgerService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Sales';

    // Clients are created through self-registration (paired 1:1 with a
    // User) — manage credit/status/assignment here, don't create from
    // scratch, to avoid orphaned Client rows with no linked account.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('company_name')->required(),
                        Forms\Components\TextInput::make('gstin')->label('GSTIN'),
                    ]),

                Forms\Components\Section::make('Credit & approval')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('credit_limit')
                            ->numeric()
                            ->prefix('₹')
                            ->required()
                            ->helperText('New clients start at ₹0 — orders fail credit check until this is set.'),
                        Forms\Components\TextInput::make('outstanding_balance')
                            ->numeric()
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Updated automatically by invoices/payments — not editable here.'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'blocked' => 'Blocked',
                            ])
                            ->required(),
                        Forms\Components\Select::make('price_tier')
                            ->options([
                                'standard' => 'Standard',
                                'wholesale' => 'Wholesale',
                                'institutional' => 'Institutional',
                            ])
                            ->default('standard')
                            ->required(),
                        Forms\Components\Select::make('assigned_staff_id')
                            ->label('Assigned staff')
                            ->options(fn () => User::whereIn('role', ['admin', 'staff'])->pluck('name', 'id'))
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Login email')->searchable(),
                Tables\Columns\TextColumn::make('credit_limit')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('outstanding_balance')->money('INR'),
                Tables\Columns\TextColumn::make('credit_available')
                    ->label('Available')
                    ->state(fn (Client $record) => $record->creditAvailable())
                    ->money('INR')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'blocked' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('assignedStaff.name')->label('Assigned to')->placeholder('Unassigned'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'blocked' => 'Blocked',
                ]),
                Tables\Filters\Filter::make('pending_approval')
                    ->label('Pending credit approval (limit = 0)')
                    ->query(fn ($query) => $query->where('credit_limit', 0)),
            ])
            ->actions([
                Tables\Actions\Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('₹')
                            ->required(),
                        Forms\Components\Select::make('mode')
                            ->options([
                                'cash' => 'Cash',
                                'cheque' => 'Cheque',
                                'neft' => 'NEFT / Bank transfer',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('reference')
                            ->label('Reference (cheque no. / UTR)'),
                    ])
                    ->modalDescription('Records an offline payment received from this client and reduces their outstanding balance immediately.')
                    ->action(function (Client $record, array $data) {
                        $payment = Payment::create([
                            'client_id' => $record->id,
                            'amount' => $data['amount'],
                            'mode' => $data['mode'],
                            'reference' => $data['reference'] ?? null,
                            'status' => 'success',
                        ]);

                        app(LedgerService::class)->post($record, 'payment', (float) $data['amount'], Payment::class, $payment->id);

                        // Email the client a payment receipt
                        $clientUser = $record->user;
                        if ($clientUser?->email) {
                            try {
                                Mail::to($clientUser->email)
                                    ->send(new PaymentReceivedMail($record->fresh(), $payment));
                            } catch (\Throwable) {
                                // Mail failure must never block payment recording
                            }
                        }

                        Notification::make()->title('Payment recorded — client notified by email.')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LedgerEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
