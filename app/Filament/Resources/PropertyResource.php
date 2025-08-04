<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Filament\Resources\PropertyResource\RelationManagers;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Diar Tunis Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('host_id')
                    ->relationship('host', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('latitude')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('longitude')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('property_type')
                    ->options([
                        'apartment' => 'Apartment',
                        'house' => 'House',
                        'villa' => 'Villa',
                        'room' => 'Room',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('number_of_guests')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\TextInput::make('number_of_bedrooms')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('number_of_beds')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('number_of_bathrooms')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('price_per_night')
                    ->numeric()
                    ->required()
                    ->prefix('TND'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('host.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('property_type')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('price_per_night')
                    ->money('TND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function (string $state): string {
                        switch ($state) {
                            case 'pending': return 'warning';
                            case 'active': return 'success';
                            case 'inactive': return 'gray';
                            case 'rejected': return 'danger';
                            default: return 'gray';
                        }
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('host')
                    ->relationship('host', 'name')
                    ->searchable()
                    ->preload(),
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
            RelationManagers\PropertyImagesRelationManager::class,
            RelationManagers\PropertyAmenitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}