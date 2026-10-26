<?php

namespace App\Filament\Resources\CctvCameras\Pages;

use App\Filament\Resources\CctvCameras\CctvCameraResource;
use Filament\Resources\Pages\EditRecord;

class EditCctvCameras extends EditRecord
{
    protected static string $resource = CctvCameraResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}