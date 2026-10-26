<?php

namespace App\Filament\Resources\ServiceClients\Pages;

use App\Filament\Resources\ServiceClients\ServiceClientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceClients extends CreateRecord
{
    protected static string $resource = ServiceClientResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}