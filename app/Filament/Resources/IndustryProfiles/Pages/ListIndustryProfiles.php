<?php

namespace App\Filament\Resources\IndustryProfiles\Pages;

use App\Filament\Resources\IndustryProfiles\IndustryProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListIndustryProfiles extends ListRecords
{
    protected static string $resource = IndustryProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}