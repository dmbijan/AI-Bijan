<?php

namespace App\Filament\Resources\LiveSupportChannels\Pages;

use App\Filament\Resources\LiveSupportChannels\LiveSupportChannelResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveSupportChannels extends EditRecord
{
    protected static string $resource = LiveSupportChannelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
