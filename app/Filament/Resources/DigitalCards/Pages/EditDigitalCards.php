<?php

namespace App\Filament\Resources\DigitalCards\Pages;

use App\Filament\Resources\DigitalCards\DigitalCardResource;
use Filament\Resources\Pages\EditRecord;

class EditDigitalCards extends EditRecord
{
    protected static string $resource = DigitalCardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
