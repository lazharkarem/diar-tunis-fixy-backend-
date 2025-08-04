<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceAppointmentResource\Pages;
use App\Filament\Resources\ServiceAppointmentResource\RelationManagers;
use App\Models\ServiceAppointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceAppointmentResource extends Resource
{
protected static ?string $model = ServiceAppointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Fixy Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider_service_id')
                    ->relationship('providerService', 'description')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Service Offered'),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Customer'),
                Forms\Components\DateTimePicker::make('appointment_time')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('providerService.serviceCategory.name')
                    ->label('Service Category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('providerService.serviceProvider.user.name')
                    ->label('Provider')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment_time')
                    ->dateTime()
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
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('providerService')
                    ->relationship('providerService', 'description')
                    ->searchable()
                    ->preload()
                    ->label('Service Offered'),
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Customer'),
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
            'index' => Pages\ListServiceAppointments::route('/'),
            'create' => Pages\CreateServiceAppointment::route('/create'),
            'edit' => Pages\EditServiceAppointment::route('/{record}/edit'),
        ];
    }
}