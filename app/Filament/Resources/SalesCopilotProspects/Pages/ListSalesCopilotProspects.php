<?php

namespace App\Filament\Resources\SalesCopilotProspects\Pages;

use App\Filament\Resources\SalesCopilotProspects\SalesCopilotProspectResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesCopilotProspects extends ListRecords
{
    protected static string $resource = SalesCopilotProspectResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
