<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebsiteTeamMemberResource\Pages;
use App\Models\WebsiteTeamMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebsiteTeamMemberResource extends Resource
{
    protected static ?string $model = WebsiteTeamMember::class;

    protected static ?string $navigationLabel = 'Team Members';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('role')->required()->maxLength(255),
                Forms\Components\TextInput::make('qualification')->maxLength(255),

                Forms\Components\Select::make('category')
                    ->options([
                        'partner'   => 'Leadership (large card)',
                        'associate' => 'Staff (compact grid)',
                    ])
                    ->required()
                    ->default('associate'),

                Forms\Components\TextInput::make('display_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first'),

                Forms\Components\Textarea::make('bio')->rows(3)->columnSpanFull(),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Photo')
                    ->image()
                    ->directory('team')
                    ->imageEditor()
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Photo')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->image_path
                        ? (str_starts_with($record->image_path, '/')
                            ? $record->image_path
                            : \Illuminate\Support\Facades\Storage::url($record->image_path))
                        : null),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors(['primary' => 'partner', 'secondary' => 'associate']),
                Tables\Columns\TextColumn::make('display_order')->label('Order')->sortable(),
            ])
            ->defaultSort('display_order')
            ->reorderable('display_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListWebsiteTeamMembers::route('/'),
            'create' => Pages\CreateWebsiteTeamMember::route('/create'),
            'edit'   => Pages\EditWebsiteTeamMember::route('/{record}/edit'),
        ];
    }
}
