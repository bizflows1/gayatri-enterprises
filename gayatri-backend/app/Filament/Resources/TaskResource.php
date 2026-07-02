<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Select::make('priority')
                    ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                    ->default('medium')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending'      => 'Pending',
                        'in_progress'  => 'In Progress',
                        'under_review' => 'Under Review',
                        'completed'    => 'Completed',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->native(false),
                Forms\Components\Select::make('assignees')
                    ->label('Assign To')
                    ->multiple()
                    ->relationship(
                        name: 'assignees',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->whereIn('role', ['admin', 'staff'])
                    )
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) { 'low' => 'success', 'medium' => 'warning', 'high' => 'danger', default => 'gray' }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) { 'pending' => 'gray', 'in_progress' => 'info', 'under_review' => 'warning', 'completed' => 'success', default => 'gray' })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'in_progress'  => 'In Progress',
                        'under_review' => 'Under Review',
                        default        => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('due_date')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->due_date && $record->due_date < now()->toDateString() && $record->status !== 'completed' ? 'danger' : null),
                Tables\Columns\TextColumn::make('assignees.name')
                    ->label('Assigned To')
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'in_progress' => 'In Progress', 'under_review' => 'Under Review', 'completed' => 'Completed']),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High']),
            ])
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
            'index'  => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit'   => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
