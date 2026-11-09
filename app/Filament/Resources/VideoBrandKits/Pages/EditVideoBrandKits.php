<?php

namespace App\Filament\Resources\VideoBrandKits\Pages;

use App\Filament\Resources\VideoBrandKits\VideoBrandKitResource;
use Filament\Resources\Pages\EditRecord;

class EditVideoBrandKits extends EditRecord
{
    protected static string $resource = VideoBrandKitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
