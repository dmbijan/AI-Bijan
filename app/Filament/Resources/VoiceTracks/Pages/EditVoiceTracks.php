<?php

namespace App\Filament\Resources\VoiceTracks\Pages;

use App\Filament\Resources\VoiceTracks\VoiceTrackResource;
use Filament\Resources\Pages\EditRecord;

class EditVoiceTracks extends EditRecord
{
    protected static string $resource = VoiceTrackResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
