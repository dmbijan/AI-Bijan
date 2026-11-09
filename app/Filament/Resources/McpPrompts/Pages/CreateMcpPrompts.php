<?php

namespace App\Filament\Resources\McpPrompts\Pages;

use App\Filament\Resources\McpPrompts\McpPromptResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMcpPrompts extends CreateRecord
{
    protected static string $resource = McpPromptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
