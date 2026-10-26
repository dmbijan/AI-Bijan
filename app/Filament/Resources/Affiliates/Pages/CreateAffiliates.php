<?php

namespace App\Filament\Resources\Affiliates\Pages;

use App\Filament\Resources\Affiliates\AffiliateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAffiliates extends CreateRecord
{
    protected static string $resource = AffiliateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}