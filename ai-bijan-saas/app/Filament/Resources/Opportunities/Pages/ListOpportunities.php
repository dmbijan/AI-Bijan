<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use Filament\Resources\Pages\ListRecords;

class ListOpportunities extends ListRecords
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}