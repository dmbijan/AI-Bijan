<?php

namespace App\Filament\Resources\FleetVehicles\Pages;

use App\Filament\Resources\FleetVehicles\FleetVehicleResource;
use Filament\Resources\Pages\ListRecords;

class ListFleetVehicles extends ListRecords
{
    protected static string $resource = FleetVehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}