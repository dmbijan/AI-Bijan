<?php

namespace App\Filament\Resources\WaContacts\Pages;

use App\Filament\Resources\WaContacts\WaContactResource;
use Filament\Resources\Pages\EditRecord;

class EditWaContacts extends EditRecord
{
    protected static string $resource = WaContactResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
