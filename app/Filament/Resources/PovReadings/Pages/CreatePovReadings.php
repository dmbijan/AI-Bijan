<?php

namespace App\Filament\Resources\PovReadings\Pages;

use App\Filament\Resources\PovReadings\PovReadingResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePovReadings extends CreateRecord
{
    protected static string $resource = PovReadingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
