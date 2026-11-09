<?php

namespace App\Filament\Resources\ResellerCommissions\Pages;

use App\Filament\Resources\ResellerCommissions\ResellerCommissionResource;
use Filament\Resources\Pages\ListRecords;

class ListResellerCommissions extends ListRecords
{
    protected static string $resource = ResellerCommissionResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
