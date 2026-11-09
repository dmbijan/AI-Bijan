<?php

namespace App\Filament\Resources\VoiceTracks\Pages;

use App\Filament\Resources\VoiceTracks\VoiceTrackResource;
use Filament\Resources\Pages\ListRecords;

class ListVoiceTracks extends ListRecords
{
    protected static string $resource = VoiceTrackResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
