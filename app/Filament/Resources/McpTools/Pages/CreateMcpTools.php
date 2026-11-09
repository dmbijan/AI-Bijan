<?php

namespace App\Filament\Resources\McpTools\Pages;

use App\Filament\Resources\McpTools\McpToolResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMcpTools extends CreateRecord
{
    protected static string $resource = McpToolResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
