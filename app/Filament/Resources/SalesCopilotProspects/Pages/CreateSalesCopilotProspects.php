<?php

namespace App\Filament\Resources\SalesCopilotProspects\Pages;

use App\Filament\Resources\SalesCopilotProspects\SalesCopilotProspectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesCopilotProspects extends CreateRecord
{
    protected static string $resource = SalesCopilotProspectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
