<?php

namespace App\Filament\Resources\EntitlementGrants\Pages;

use App\Filament\Resources\EntitlementGrants\EntitlementGrantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEntitlementGrants extends CreateRecord
{
    protected static string $resource = EntitlementGrantResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
