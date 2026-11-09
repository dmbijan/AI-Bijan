<?php

namespace App\Filament\Resources\BrainInsights\Pages;

use App\Filament\Resources\BrainInsights\InsightResource;
use Filament\Resources\Pages\ListRecords;

class ListBrainInsights extends ListRecords
{
    protected static string $resource = InsightResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
