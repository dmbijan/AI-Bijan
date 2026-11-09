<?php

namespace App\Filament\Resources\DigitalCards\Pages;

use App\Filament\Resources\DigitalCards\DigitalCardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDigitalCards extends CreateRecord
{
    protected static string $resource = DigitalCardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
