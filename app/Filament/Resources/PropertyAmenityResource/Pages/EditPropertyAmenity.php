<?php

namespace App\Filament\Resources\PropertyAmenityResource\Pages;

use App\Filament\Resources\PropertyAmenityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPropertyAmenity extends EditRecord
{
    protected static string $resource = PropertyAmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
