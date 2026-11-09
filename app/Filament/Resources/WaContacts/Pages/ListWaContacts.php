<?php

namespace App\Filament\Resources\WaContacts\Pages;

use App\Filament\Resources\WaContacts\WaContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaContacts extends ListRecords
{
    protected static string $resource = WaContactResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
