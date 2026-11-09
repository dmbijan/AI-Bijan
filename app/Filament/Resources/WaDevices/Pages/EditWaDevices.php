<?php

namespace App\Filament\Resources\WaDevices\Pages;

use App\Filament\Resources\WaDevices\WaDeviceResource;
use Filament\Resources\Pages\EditRecord;

class EditWaDevices extends EditRecord
{
    protected static string $resource = WaDeviceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
