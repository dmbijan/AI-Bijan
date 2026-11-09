<?php

namespace App\Filament\Resources\LiveStreamDestinations\Pages;

use App\Filament\Resources\LiveStreamDestinations\LiveStreamDestinationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveStreamDestinations extends CreateRecord
{
    protected static string $resource = LiveStreamDestinationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
