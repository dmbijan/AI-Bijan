<?php

namespace App\Filament\Resources\McpAuditLogs\Pages;

use App\Filament\Resources\McpAuditLogs\McpAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListMcpAuditLogs extends ListRecords
{
    protected static string $resource = McpAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
