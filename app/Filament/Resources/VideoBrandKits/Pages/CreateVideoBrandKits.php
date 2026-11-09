<?php

namespace App\Filament\Resources\VideoBrandKits\Pages;

use App\Filament\Resources\VideoBrandKits\VideoBrandKitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVideoBrandKits extends CreateRecord
{
    protected static string $resource = VideoBrandKitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
