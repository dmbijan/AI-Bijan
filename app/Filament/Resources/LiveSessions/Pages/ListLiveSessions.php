<?php

namespace App\Filament\Resources\LiveSessions\Pages;

use App\Filament\Resources\LiveSessions\LiveSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListLiveSessions extends ListRecords
{
    protected static string $resource = LiveSessionResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
