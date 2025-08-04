<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
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
                    ->label('Paid By'),
                Forms\Components\Select::make('payable_type')
                    ->options([
                        'App\Models\Booking' => 'Booking',
                        'App\Models\ServiceAppointment' => 'Service Appointment',
                    ])
                    ->required()
                    ->live()
                    ->label('Payable Type'),
                Forms\Components\Select::make('payable_id')
                    ->label('Payable Item')
                    ->options(function (Forms\Get $get) {
                        $type = $get('payable_type');
                        if (!$type) {
                            return [];
                        }
                        $model = $type::query();
                        if ($type === 'App\Models\Booking') {
                            return $model->pluck('total_price', 'id');
                        } elseif ($type === 'App\Models\ServiceAppointment') {
                            return $model->pluck('appointment_time', 'id');
                        }
                        return [];
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->prefix('TND'),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('TND'),
                Forms\Components\TextInput::make('payment_method')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('transaction_id')
                    ->required()
                    ->maxLength(255)->unique(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
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
                    ->label('Paid By'),
                Tables\Columns\TextColumn::make('payable_type')
                    ->label('Payable Type')
                    ->formatStateUsing(function (string $state): string {
                        return str_replace('App\\Models\\', '', $state);
                    })
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('TND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function (string $state): string {
                        switch ($state) {
                            case 'pending': return 'warning';
                            case 'completed': return 'success';
                            case 'failed': return 'danger';
                            case 'refunded': return 'info';
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
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'credit_card' => 'Credit Card',
                        'paypal' => 'PayPal',
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}