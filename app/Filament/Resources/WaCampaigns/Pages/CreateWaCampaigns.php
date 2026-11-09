<?php

namespace App\Filament\Resources\WaCampaigns\Pages;

use App\Filament\Resources\WaCampaigns\WaCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWaCampaigns extends CreateRecord
{
    protected static string $resource = WaCampaignResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
