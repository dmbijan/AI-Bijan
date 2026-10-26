<?php

namespace App\Filament\Resources\FleetVehicles\Pages;

use App\Filament\Resources\FleetVehicles\FleetVehicleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFleetVehicles extends CreateRecord
{
    protected static string $resource = FleetVehicleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}