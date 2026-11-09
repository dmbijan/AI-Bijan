<?php

namespace App\Filament\Resources\McpSessions\Pages;

use App\Filament\Resources\McpSessions\McpSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListMcpSessions extends ListRecords
{
    protected static string $resource = McpSessionResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
