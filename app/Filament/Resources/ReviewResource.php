<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Shared Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Reviewer'),
                Forms\Components\Select::make('reviewable_type')
                    ->options([
                        'App\Models\Property' => 'Property',
                        'App\Models\Experience' => 'Experience',
                        'App\Models\ServiceProvider' => 'Service Provider',
                    ])
                    ->required()
                    ->live()
                    ->label('Reviewable Type'),
                Forms\Components\Select::make('reviewable_id')
                    ->label('Reviewable Item')
                    ->options(function (Forms\Get $get) {
                        $type = $get('reviewable_type');
                        if (!$type) {
                            return [];
                        }
                        $model = $type::query();
                        if ($type === 'App\Models\Property' || $type === 'App\Models\Experience') {
                            return $model->pluck('title', 'id');
                        } elseif ($type === 'App\Models\ServiceProvider') {
                            return $model->with('user')->get()->pluck('user.name', 'id');
                        }
                        return [];
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('rating')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(5),
                Forms\Components\Textarea::make('comment')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Reviewer'),
                Tables\Columns\TextColumn::make('reviewable_type')
                    ->label('Type')
                    ->formatStateUsing(function (string $state): string {
                        return str_replace('App\\Models\\', '', $state);
                    })
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('reviewable.title')
                    ->label('Item Title/Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comment')
                    ->limit(50)
                    ->toggleable(true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('reviewable_type')
                    ->options([
                        'App\Models\Property' => 'Property',
                        'App\Models\Experience' => 'Experience',
                        'App\Models\ServiceProvider' => 'Service Provider',
                    ])
                    ->label('Reviewable Type'),
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}