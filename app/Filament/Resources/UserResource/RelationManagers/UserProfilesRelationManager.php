<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UserProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'profile';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('bio')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\Select::make('id_verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('bank_account_details')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable()
                    ->label('Bank Account Details (Encrypted)'),
                Forms\Components\Textarea::make('qualifications')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable()
                    ->label('Qualifications (for Service Providers)'),
                Forms\Components\TextInput::make('service_areas')
                    ->maxLength(255)
                    ->nullable()
                    ->label('Service Areas (comma-separated)'),
                Forms\Components\TextInput::make('response_rate')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->nullable(),
                Forms\Components\DateTimePicker::make('last_active')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id_verification_status')
            ->columns([
                Tables\Columns\TextColumn::make('id_verification_status')
                    ->searchable()
                    ->badge()
                    ->color(function (string $state): string {
                        switch ($state) {
                            case 'pending': return 'warning';
                            case 'verified': return 'success';
                            case 'rejected': return 'danger';
                            default: return 'gray';
                        }
                    }),
                Tables\Columns\TextColumn::make('response_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_active')
                    ->dateTime()
                    ->sortable(),
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