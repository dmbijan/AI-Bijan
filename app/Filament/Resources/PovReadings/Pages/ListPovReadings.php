<?php

namespace App\Filament\Resources\PovReadings\Pages;

use App\Filament\Resources\PovReadings\PovReadingResource;
use Filament\Resources\Pages\ListRecords;

class ListPovReadings extends ListRecords
{
    protected static string $resource = PovReadingResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
