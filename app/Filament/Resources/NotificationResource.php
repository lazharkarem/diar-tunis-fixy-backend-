<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'Shared Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('notifiable_type')
                    ->options([
                        'App\Models\User' => 'User',
                        // Add other notifiable types if applicable
                    ])
                    ->required()
                    ->live()
                    ->label('Notifiable Type'),
                Forms\Components\Select::make('notifiable_id')
                    ->label('Notifiable User')
                    ->options(function (Forms\Get $get) {
                        $type = $get('notifiable_type');
                        if (!$type) {
                            return [];
                        }
                        // Assuming 'User' is the primary notifiable model
                        if ($type == 'App\Models\User') {
                            return \App\Models\User::pluck('name', 'id');
                        }
                        return [];
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('data')
                    ->required()
                    ->columnSpanFull()
                    ->label('Notification Data (JSON)'),
                Forms\Components\DateTimePicker::make('read_at')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('notifiable.name')
                    ->label('Notifiable User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('read_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'App\Notifications\BookingConfirmed' => 'Booking Confirmed',
                        'App\Notifications\ServiceAppointmentScheduled' => 'Service Appointment',
                        // Add other notification types
                    ]),
                Tables\Filters\TernaryFilter::make('read_at')
                    ->label('Read Status')
                    ->nullable(false)
                    ->trueLabel('Read')
                    ->falseLabel('Unread'),
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}