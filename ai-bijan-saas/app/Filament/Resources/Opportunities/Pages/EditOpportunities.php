<?php

namespace App\Filament\Resources\Opportunities\Pages;

use App\Filament\Resources\Opportunities\OpportunityResource;
use Filament\Resources\Pages\EditRecord;

class EditOpportunities extends EditRecord
{
    protected static string $resource = OpportunityResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}