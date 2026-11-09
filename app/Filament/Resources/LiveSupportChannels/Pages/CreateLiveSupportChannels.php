<?php

namespace App\Filament\Resources\LiveSupportChannels\Pages;

use App\Filament\Resources\LiveSupportChannels\LiveSupportChannelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveSupportChannels extends CreateRecord
{
    protected static string $resource = LiveSupportChannelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
