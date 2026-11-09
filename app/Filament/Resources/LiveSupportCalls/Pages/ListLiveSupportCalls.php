<?php

namespace App\Filament\Resources\LiveSupportCalls\Pages;

use App\Filament\Resources\LiveSupportCalls\LiveSupportCallResource;
use Filament\Resources\Pages\ListRecords;

class ListLiveSupportCalls extends ListRecords
{
    protected static string $resource = LiveSupportCallResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
