<?php

namespace App\Filament\Resources\LiveControls\Pages;

use App\Filament\Resources\LiveControls\LiveControlResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLiveControls extends CreateRecord
{
    protected static string $resource = LiveControlResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
