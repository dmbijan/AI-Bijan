<?php

namespace App\Filament\Resources\ServiceClients\Pages;

use App\Filament\Resources\ServiceClients\ServiceClientResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceClients extends ListRecords
{
    protected static string $resource = ServiceClientResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}