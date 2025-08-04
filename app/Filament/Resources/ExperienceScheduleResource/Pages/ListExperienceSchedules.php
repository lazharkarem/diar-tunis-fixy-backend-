<?php

namespace App\Filament\Resources\ExperienceScheduleResource\Pages;

use App\Filament\Resources\ExperienceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExperienceSchedules extends ListRecords
{
    protected static string $resource = ExperienceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
