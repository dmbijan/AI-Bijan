<?php

namespace App\Filament\Resources\CodOrders\Pages;

use App\Filament\Resources\CodOrders\CodOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListCodOrders extends ListRecords
{
    protected static string $resource = CodOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}