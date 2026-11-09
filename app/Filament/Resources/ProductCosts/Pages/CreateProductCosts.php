<?php

namespace App\Filament\Resources\ProductCosts\Pages;

use App\Filament\Resources\ProductCosts\ProductCostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCosts extends CreateRecord
{
    protected static string $resource = ProductCostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
