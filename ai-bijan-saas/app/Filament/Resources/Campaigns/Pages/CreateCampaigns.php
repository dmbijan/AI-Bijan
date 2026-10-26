<?php

namespace App\Filament\Resources\Campaigns\Pages;

use App\Filament\Resources\Campaigns\CampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCampaigns extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}