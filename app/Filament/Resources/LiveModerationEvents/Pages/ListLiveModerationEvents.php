<?php

namespace App\Filament\Resources\LiveModerationEvents\Pages;

use App\Filament\Resources\LiveModerationEvents\LiveModerationEventResource;
use Filament\Resources\Pages\ListRecords;

class ListLiveModerationEvents extends ListRecords
{
    protected static string $resource = LiveModerationEventResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
