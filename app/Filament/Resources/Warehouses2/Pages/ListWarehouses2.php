<?php

namespace App\Filament\Resources\Warehouses2\Pages;

use App\Filament\Resources\Warehouses2\WarehouseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarehouses2 extends ListRecords
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
