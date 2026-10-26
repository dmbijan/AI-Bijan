<?php

namespace App\Filament\Resources\OmniChannels\Pages;

use App\Filament\Resources\OmniChannels\OmniChannelResource;
use Filament\Resources\Pages\EditRecord;

class EditOmniChannels extends EditRecord
{
    protected static string $resource = OmniChannelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}