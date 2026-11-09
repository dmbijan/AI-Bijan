<?php

namespace App\Filament\Resources\EntitlementGrants\Pages;

use App\Filament\Resources\EntitlementGrants\EntitlementGrantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEntitlementGrants extends ListRecords
{
    protected static string $resource = EntitlementGrantResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
