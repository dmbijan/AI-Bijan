<?php

namespace App\Filament\Resources\VoiceProfiles\Pages;

use App\Filament\Resources\VoiceProfiles\VoiceProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVoiceProfiles extends CreateRecord
{
    protected static string $resource = VoiceProfileResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
