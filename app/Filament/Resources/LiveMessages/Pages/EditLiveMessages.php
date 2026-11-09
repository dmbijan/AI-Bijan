<?php

namespace App\Filament\Resources\LiveMessages\Pages;

use App\Filament\Resources\LiveMessages\LiveMessageResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveMessages extends EditRecord
{
    protected static string $resource = LiveMessageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
