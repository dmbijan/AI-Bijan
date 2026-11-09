<?php

namespace App\Filament\Resources\WaCampaigns\Pages;

use App\Filament\Resources\WaCampaigns\WaCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaCampaigns extends ListRecords
{
    protected static string $resource = WaCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
