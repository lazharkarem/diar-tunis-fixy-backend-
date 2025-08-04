<?php

namespace App\Filament\Resources\ServiceAppointmentResource\Pages;

use App\Filament\Resources\ServiceAppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceAppointment extends EditRecord
{
    protected static string $resource = ServiceAppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
