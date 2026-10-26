<?php

namespace App\Filament\Resources\IndustryProfiles\Pages;

use App\Filament\Resources\IndustryProfiles\IndustryProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIndustryProfiles extends CreateRecord
{
    protected static string $resource = IndustryProfileResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}