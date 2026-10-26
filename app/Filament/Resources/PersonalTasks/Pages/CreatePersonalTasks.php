<?php

namespace App\Filament\Resources\PersonalTasks\Pages;

use App\Filament\Resources\PersonalTasks\PersonalTaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonalTasks extends CreateRecord
{
    protected static string $resource = PersonalTaskResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}