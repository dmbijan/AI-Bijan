<?php

namespace App\Filament\Resources\BrainForecasts\Pages;

use App\Filament\Resources\BrainForecasts\ForecastResource;
use Filament\Resources\Pages\ListRecords;

class ListBrainForecasts extends ListRecords
{
    protected static string $resource = ForecastResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
