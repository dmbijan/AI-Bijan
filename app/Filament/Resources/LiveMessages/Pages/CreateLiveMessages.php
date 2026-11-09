<?php

namespace App\Filament\Resources\LiveMessages\Pages;

use App\Filament\Resources\LiveMessages\LiveMessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveMessages extends CreateRecord
{
    protected static string $resource = LiveMessageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
