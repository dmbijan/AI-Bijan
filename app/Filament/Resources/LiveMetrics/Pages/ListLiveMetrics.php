<?php

namespace App\Filament\Resources\LiveMetrics\Pages;

use App\Filament\Resources\LiveMetrics\LiveMetricResource;
use Filament\Resources\Pages\ListRecords;

class ListLiveMetrics extends ListRecords
{
    protected static string $resource = LiveMetricResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
