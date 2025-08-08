<?php

namespace App\Filament\Resources\ServiceProviderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProviderServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_category_id')
                    ->relationship('serviceCategory', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Service Category'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->nullable()
                    ->prefix('TND'),  // Changed from 'IND' to 'TND' for consistency
                Forms\Components\TextInput::make('unit')
                    ->maxLength(50)
                    ->nullable()
                    ->label('Unit (e.g., per hour, per service)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('serviceCategory.name')
                    ->searchable()
                    ->sortable()
                    ->label('Category'),
                Tables\Columns\TextColumn::make('price')
                    ->money('TND')  // Changed from 'IND' to 'TND' for consistency
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
