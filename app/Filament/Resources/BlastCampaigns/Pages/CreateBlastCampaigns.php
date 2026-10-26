<?php

namespace App\Filament\Resources\BlastCampaigns\Pages;

use App\Filament\Resources\BlastCampaigns\BlastCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlastCampaigns extends CreateRecord
{
    protected static string $resource = BlastCampaignResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}