<?php

namespace App\Filament\Resources\Warehouses2\Pages;

use App\Filament\Resources\Warehouses2\WarehouseResource;
use Filament\Resources\Pages\ListRecords;

class ListWarehouses2 extends ListRecords
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}