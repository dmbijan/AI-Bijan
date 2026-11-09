<?php

namespace App\Filament\Resources\LiveMessages\Pages;

use App\Filament\Resources\LiveMessages\LiveMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListLiveMessages extends ListRecords
{
    protected static string $resource = LiveMessageResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
