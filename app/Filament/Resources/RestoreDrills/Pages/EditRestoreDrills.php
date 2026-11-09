<?php

namespace App\Filament\Resources\RestoreDrills\Pages;

use App\Filament\Resources\RestoreDrills\RestoreDrillResource;
use Filament\Resources\Pages\EditRecord;

class EditRestoreDrills extends EditRecord
{
    protected static string $resource = RestoreDrillResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
