<?php

namespace App\Filament\Resources\ExperienceScheduleResource\Pages;

use App\Filament\Resources\ExperienceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExperienceSchedule extends EditRecord
{
    protected static string $resource = ExperienceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
