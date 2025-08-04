<?php

namespace App\Filament\Resources\PropertyImageResource\Pages;

use App\Filament\Resources\PropertyImageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPropertyImage extends EditRecord
{
    protected static string $resource = PropertyImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
