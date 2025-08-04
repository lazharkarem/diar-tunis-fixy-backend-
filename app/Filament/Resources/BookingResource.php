<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    
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
                    ->label('Booked By'),
                Forms\Components\Select::make('bookable_type')
                    ->options([
                        'App\Models\Property' => 'Property',
                        'App\Models\Experience' => 'Experience',
                    ])
                    ->required()
                    ->live()
                    ->label('Bookable Type'),
                Forms\Components\Select::make('bookable_id')
                    ->label('Bookable Item')
                    ->options(function (Forms\Get $get) {
                        $type = $get('bookable_type');
                        if (!$type) {
                            return [];
                        }
                        $model = $type::query();
                        return $model->pluck('title', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('start_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('end_date')
                    ->required()
                    ->afterOrEqual('start_date'),
                Forms\Components\TextInput::make('number_of_guests')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\TextInput::make('total_price')
                    ->numeric()
                    ->required()
                    ->prefix('TND'),  // Fixed from 'TWO' to 'TND'
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Booked By'),
                Tables\Columns\TextColumn::make('bookable_type')
                    ->label('Type')
                    ->formatStateUsing(function (string $state): string {
                        return str_replace('App\\Models\\', '', $state);
                    })
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('bookable.title')
                    ->label('Item Title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('TND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function (string $state): string {
                        switch ($state) {
                            case 'pending': return 'warning';
                            case 'confirmed': return 'info';
                            case 'completed': return 'success';
                            case 'cancelled': return 'danger';
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
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('bookable_type')
                    ->options([
                        'App\Models\Property' => 'Property',
                        'App\Models\Experience' => 'Experience',
                    ])
                    ->label('Bookable Type'),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}