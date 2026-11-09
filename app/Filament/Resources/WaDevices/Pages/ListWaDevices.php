<?php

namespace App\Filament\Resources\WaDevices\Pages;

use App\Filament\Resources\WaDevices\WaDeviceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaDevices extends ListRecords
{
    protected static string $resource = WaDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
