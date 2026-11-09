<?php

namespace App\Filament\Resources\PosOnlineStores\Pages;

use App\Filament\Resources\PosOnlineStores\PosOnlineStoreResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosOnlineStores extends CreateRecord
{
    protected static string $resource = PosOnlineStoreResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
