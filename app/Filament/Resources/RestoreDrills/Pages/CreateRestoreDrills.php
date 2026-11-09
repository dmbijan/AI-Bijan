<?php

namespace App\Filament\Resources\RestoreDrills\Pages;

use App\Filament\Resources\RestoreDrills\RestoreDrillResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRestoreDrills extends CreateRecord
{
    protected static string $resource = RestoreDrillResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
