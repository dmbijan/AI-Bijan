<?php

namespace App\Filament\Resources\LiveSessions\Pages;

use App\Filament\Resources\LiveSessions\LiveSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveSessions extends CreateRecord
{
    protected static string $resource = LiveSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
