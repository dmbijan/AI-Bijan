<?php

namespace App\Filament\Resources\LiveModerationEvents\Pages;

use App\Filament\Resources\LiveModerationEvents\LiveModerationEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveModerationEvents extends CreateRecord
{
    protected static string $resource = LiveModerationEventResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
