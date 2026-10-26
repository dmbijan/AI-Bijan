<?php

namespace App\Filament\Resources\CctvCameras\Pages;

use App\Filament\Resources\CctvCameras\CctvCameraResource;
use Filament\Resources\Pages\ListRecords;

class ListCctvCameras extends ListRecords
{
    protected static string $resource = CctvCameraResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}