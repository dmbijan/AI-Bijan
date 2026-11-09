<?php

namespace App\Filament\Resources\ResellerCommissions\Pages;

use App\Filament\Resources\ResellerCommissions\ResellerCommissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateResellerCommissions extends CreateRecord
{
    protected static string $resource = ResellerCommissionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
