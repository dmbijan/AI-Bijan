<?php

namespace App\Filament\Resources\LiveHostPersonalities\Pages;

use App\Filament\Resources\LiveHostPersonalities\LiveHostPersonalityResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveHostPersonalities extends EditRecord
{
    protected static string $resource = LiveHostPersonalityResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
