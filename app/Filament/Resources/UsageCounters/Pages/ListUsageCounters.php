<?php

namespace App\Filament\Resources\UsageCounters\Pages;

use App\Filament\Resources\UsageCounters\UsageCounterResource;
use Filament\Resources\Pages\ListRecords;

class ListUsageCounters extends ListRecords
{
    protected static string $resource = UsageCounterResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
