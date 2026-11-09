<?php

namespace App\Filament\Resources\WaCampaigns\Pages;

use App\Filament\Resources\WaCampaigns\WaCampaignResource;
use Filament\Resources\Pages\EditRecord;

class EditWaCampaigns extends EditRecord
{
    protected static string $resource = WaCampaignResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
