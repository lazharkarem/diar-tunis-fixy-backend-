<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)->unique(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(function (string $state): string {
                        return Hash::make($state);
                    })
                    ->dehydrated(function ($state): bool {
                        return filled($state);
                    })
                    ->required(function (string $operation): bool {
                        return $operation === 'create';
                    })
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                    ->maxLength(20)->unique()
                    ->nullable(),
                Forms\Components\Select::make('user_type')
                    ->options([
                        'guest' => 'Guest',
                        'host' => 'Host',
                        'service_customer' => 'Service Customer',
                        'service_provider' => 'Service Provider',
                        'admin' => 'Admin',
                    ])
                    ->required()
                    ->default('guest'),
                Forms\Components\FileUpload::make('profile_picture')
                    ->image()
                    ->directory('profile-pictures')
                    ->nullable(),
                Forms\Components\Textarea::make('address')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(true),
                Tables\Columns\TextColumn::make('user_type')
                    ->searchable()
                    ->badge(),
                Tables\Columns\ImageColumn::make('profile_picture')
                    ->circular(),
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
                Tables\Filters\SelectFilter::make('user_type')
                    ->options([
                        'guest' => 'Guest',
                        'host' => 'Host',
                        'service_customer' => 'Service Customer',
                        'service_provider' => 'Service Provider',
                        'admin' => 'Admin',
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
           RelationManagers\UserProfilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}