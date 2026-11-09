<?php

namespace App\Filament\Resources\LiveStreamDestinations\Pages;

use App\Filament\Resources\LiveStreamDestinations\LiveStreamDestinationResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveStreamDestinations extends EditRecord
{
    protected static string $resource = LiveStreamDestinationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
