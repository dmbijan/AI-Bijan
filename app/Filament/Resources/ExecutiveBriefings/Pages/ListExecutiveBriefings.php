<?php

namespace App\Filament\Resources\ExecutiveBriefings\Pages;

use App\Filament\Resources\ExecutiveBriefings\ExecutiveBriefingResource;
use Filament\Resources\Pages\ListRecords;

class ListExecutiveBriefings extends ListRecords
{
    protected static string $resource = ExecutiveBriefingResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
