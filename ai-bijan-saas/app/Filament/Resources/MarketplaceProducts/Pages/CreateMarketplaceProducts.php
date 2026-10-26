<?php

namespace App\Filament\Resources\MarketplaceProducts\Pages;

use App\Filament\Resources\MarketplaceProducts\MarketplaceProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketplaceProducts extends CreateRecord
{
    protected static string $resource = MarketplaceProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}