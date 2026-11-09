<?php

namespace App\Filament\Resources\AiUsageEvents\Pages;

use App\Filament\Resources\AiUsageEvents\AiUsageEventResource;
use Filament\Resources\Pages\ListRecords;

class ListAiUsageEvents extends ListRecords
{
    protected static string $resource = AiUsageEventResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
