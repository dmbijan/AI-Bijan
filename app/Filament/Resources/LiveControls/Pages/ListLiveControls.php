<?php

namespace App\Filament\Resources\LiveControls\Pages;

use App\Filament\Resources\LiveControls\LiveControlResource;
use Filament\Resources\Pages\ListRecords;

class ListLiveControls extends ListRecords
{
    protected static string $resource = LiveControlResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
