<?php

namespace App\Filament\Resources\BlastCampaigns\Pages;

use App\Filament\Resources\BlastCampaigns\BlastCampaignResource;
use Filament\Resources\Pages\ListRecords;

class ListBlastCampaigns extends ListRecords
{
    protected static string $resource = BlastCampaignResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}