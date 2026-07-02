<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                        $set('slug', Str::slug($state))
                    )
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Blog::class, 'slug', ignoreRecord: true)
                    ->columnSpanFull(),

                Forms\Components\Select::make('category')
                    ->options([
                        'Compliance'     => 'Compliance',
                        'Technical Guide'=> 'Technical Guide',
                        'Market Trends'  => 'Market Trends',
                        'Safety'         => 'Safety',
                        'Company News'   => 'Company News',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('author')
                    ->default('Gayatri Insights AI')
                    ->required(),

                Forms\Components\Textarea::make('excerpt')
                    ->maxLength(200)
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('image_url')
                    ->label('Cover Image URL')
                    ->url()
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Published At')
                    ->default(now()),

                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(60),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary'   => 'Technical Guide',
                        'success'   => 'Compliance',
                        'warning'   => 'Market Trends',
                        'danger'    => 'Safety',
                        'secondary' => 'Company News',
                    ]),
                Tables\Columns\TextColumn::make('author')->label('Author'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Compliance'      => 'Compliance',
                        'Technical Guide' => 'Technical Guide',
                        'Market Trends'   => 'Market Trends',
                        'Safety'          => 'Safety',
                        'Company News'    => 'Company News',
                    ]),
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
            'index'  => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit'   => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
