<?php

namespace App\Filament\Resources\Warehouses2\Pages;

use App\Filament\Resources\Warehouses2\WarehouseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouses2 extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}