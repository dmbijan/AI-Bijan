<?php

namespace App\Filament\Resources\LiveConsentRecords\Pages;

use App\Filament\Resources\LiveConsentRecords\LiveConsentRecordResource;
use Filament\Resources\Pages\ListRecords;

class ListLiveConsentRecords extends ListRecords
{
    protected static string $resource = LiveConsentRecordResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
