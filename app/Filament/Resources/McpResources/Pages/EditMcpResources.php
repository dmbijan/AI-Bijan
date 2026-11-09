<?php

namespace App\Filament\Resources\McpResources\Pages;

use App\Filament\Resources\McpResources\McpResourceResource;
use Filament\Resources\Pages\EditRecord;

class EditMcpResources extends EditRecord
{
    protected static string $resource = McpResourceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
