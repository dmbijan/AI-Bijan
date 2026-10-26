<?php

namespace App\Filament\Resources\OmniChannels\Pages;

use App\Filament\Resources\OmniChannels\OmniChannelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOmniChannels extends CreateRecord
{
    protected static string $resource = OmniChannelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}