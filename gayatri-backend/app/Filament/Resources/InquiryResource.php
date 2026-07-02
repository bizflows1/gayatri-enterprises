<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->disabled(),
                Forms\Components\TextInput::make('email')->disabled(),
                Forms\Components\TextInput::make('institution')->disabled()->visible(fn ($record) => $record?->source === 'contact'),
                Forms\Components\TextInput::make('company')->disabled()->visible(fn ($record) => $record?->source === 'bulk_order'),
                Forms\Components\TextInput::make('industry')->disabled()->visible(fn ($record) => $record?->source === 'bulk_order'),
                Forms\Components\TextInput::make('contact_person')->disabled()->visible(fn ($record) => $record?->source === 'bulk_order'),
                Forms\Components\TextInput::make('type')->label('Requirement Type')->disabled()->visible(fn ($record) => $record?->source === 'contact'),
                Forms\Components\Textarea::make('message')->disabled()->columnSpanFull()->visible(fn ($record) => $record?->source === 'contact'),
                Forms\Components\Textarea::make('requirements')->disabled()->columnSpanFull()->visible(fn ($record) => $record?->source === 'bulk_order'),
                Forms\Components\Toggle::make('needs_msds_coa')->label('Needs MSDS / COA')->disabled()->visible(fn ($record) => $record?->source === 'bulk_order'),
            ])->columns(2),

            Forms\Components\Section::make('Follow-up')->schema([
                Forms\Components\Select::make('status')
                    ->options(['new' => 'New', 'in_progress' => 'In Progress', 'closed' => 'Closed'])
                    ->required(),
                Forms\Components\Textarea::make('notes')->label('Internal Notes')->rows(3)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->color(fn (string $state) => match ($state) { 'contact' => 'info', 'bulk_order' => 'warning', default => 'gray' })
                    ->formatStateUsing(fn (string $state) => $state === 'bulk_order' ? 'Bulk Order' : 'Contact'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('company')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) { 'closed' => 'success', 'in_progress' => 'warning', 'new' => 'danger', default => 'gray' }),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->options(['contact' => 'Contact Form', 'bulk_order' => 'Bulk Order']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['new' => 'New', 'in_progress' => 'In Progress', 'closed' => 'Closed']),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('View / Update'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
            'edit'  => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }
}
