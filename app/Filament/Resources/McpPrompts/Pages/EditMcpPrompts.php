<?php

namespace App\Filament\Resources\McpPrompts\Pages;

use App\Filament\Resources\McpPrompts\McpPromptResource;
use Filament\Resources\Pages\EditRecord;

class EditMcpPrompts extends EditRecord
{
    protected static string $resource = McpPromptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
