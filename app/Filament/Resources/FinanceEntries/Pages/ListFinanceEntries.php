<?php

namespace App\Filament\Resources\FinanceEntries\Pages;

use App\Filament\Resources\FinanceEntries\FinanceEntryResource;
use Filament\Resources\Pages\ListRecords;

class ListFinanceEntries extends ListRecords
{
    protected static string $resource = FinanceEntryResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}