<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderServiceResource\Pages;
use App\Models\ServiceProvider;
use App\Models\ProviderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProviderServiceResource extends Resource
{
    protected static ?string $model = ProviderService::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Fixy Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_provider_id')
                    ->label('Service Provider')
                    ->options(
                        ServiceProvider::with('user')
                            ->get()
                            ->mapWithKeys(fn ($provider) => [
                                $provider->id => $provider->user->name
                            ])
                    )
                    ->required()
                    ->searchable(),

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
                    ->prefix('TND'),

                Forms\Components\TextInput::make('unit')
                    ->maxLength(50)
                    ->nullable()
                    ->label('Unit (e.g., per hour, per service)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serviceProvider.user.name')
                    ->label('Service Provider')
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('serviceProvider.user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('serviceCategory.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('TND')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->searchable()
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_provider_id')
                    ->label('Service Provider')
                    ->options(
                        ServiceProvider::with('user')
                            ->get()
                            ->mapWithKeys(fn ($provider) => [
                                $provider->id => $provider->user->name
                            ])
                    )
                    ->searchable(),

                Tables\Filters\SelectFilter::make('service_category_id')
                    ->relationship('serviceCategory', 'name')
                    ->label('Service Category')
                    ->searchable(),
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
            'index' => Pages\ListProviderServices::route('/'),
            'create' => Pages\CreateProviderService::route('/create'),
            'edit' => Pages\EditProviderService::route('/{record}/edit'),
        ];
    }

   
}