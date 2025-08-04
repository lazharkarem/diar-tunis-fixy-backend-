<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyImageResource\Pages;
use App\Filament\Resources\PropertyImageResource\RelationManagers;
use App\Models\PropertyImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PropertyImageResource extends Resource
{
    protected static ?string $model = PropertyImage::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Diar Tunis Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\FileUpload::make('image_url')
                    ->label('Image File')
                    ->image()
                    ->required()
                    ->directory('property-images'),
                Forms\Components\Toggle::make('is_thumbnail')
                    ->label('Set as Thumbnail')
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->square(),
                Tables\Columns\IconColumn::make('is_thumbnail')
                    ->boolean()
                    ->label('Thumbnail'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('property')
                    ->relationship('property', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_thumbnail')
                    ->label('Is Thumbnail')
                    ->boolean(),
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
            'index' => Pages\ListPropertyImages::route('/'),
            'create' => Pages\CreatePropertyImage::route('/create'),
            'edit' => Pages\EditPropertyImage::route('/{record}/edit'),
        ];
    }
}