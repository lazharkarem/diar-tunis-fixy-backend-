<?php

namespace App\Filament\Resources\PropertyAmenityResource\Pages;

use App\Filament\Resources\PropertyAmenityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPropertyAmenities extends ListRecords
{
    protected static string $resource = PropertyAmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
