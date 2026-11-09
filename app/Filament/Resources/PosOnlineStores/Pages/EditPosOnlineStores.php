<?php

namespace App\Filament\Resources\PosOnlineStores\Pages;

use App\Filament\Resources\PosOnlineStores\PosOnlineStoreResource;
use Filament\Resources\Pages\EditRecord;

class EditPosOnlineStores extends EditRecord
{
    protected static string $resource = PosOnlineStoreResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
