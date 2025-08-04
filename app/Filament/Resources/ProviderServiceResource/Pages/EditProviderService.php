<?php

namespace App\Filament\Resources\ProviderServiceResource\Pages;

use App\Filament\Resources\ProviderServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProviderService extends EditRecord
{
    protected static string $resource = ProviderServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
