<?php

namespace App\Filament\Resources\VoiceProfiles\Pages;

use App\Filament\Resources\VoiceProfiles\VoiceProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVoiceProfiles extends ListRecords
{
    protected static string $resource = VoiceProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
