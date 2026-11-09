<?php

namespace App\Filament\Resources\RestoreDrills\Pages;

use App\Filament\Resources\RestoreDrills\RestoreDrillResource;
use Filament\Resources\Pages\ListRecords;

class ListRestoreDrills extends ListRecords
{
    protected static string $resource = RestoreDrillResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
