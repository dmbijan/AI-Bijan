<?php

namespace App\Filament\Resources\McpSessions\Pages;

use App\Filament\Resources\McpSessions\McpSessionResource;
use Filament\Resources\Pages\EditRecord;

class EditMcpSessions extends EditRecord
{
    protected static string $resource = McpSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
