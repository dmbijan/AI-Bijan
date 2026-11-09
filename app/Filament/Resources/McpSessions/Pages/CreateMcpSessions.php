<?php

namespace App\Filament\Resources\McpSessions\Pages;

use App\Filament\Resources\McpSessions\McpSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMcpSessions extends CreateRecord
{
    protected static string $resource = McpSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
