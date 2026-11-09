<?php

namespace App\Filament\Resources\McpAuditLogs\Pages;

use App\Filament\Resources\McpAuditLogs\McpAuditLogResource;
use Filament\Resources\Pages\EditRecord;

class EditMcpAuditLogs extends EditRecord
{
    protected static string $resource = McpAuditLogResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
