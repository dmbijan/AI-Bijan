<?php

namespace App\Filament\Resources\McpTools\Pages;

use App\Filament\Resources\McpTools\McpToolResource;
use Filament\Resources\Pages\EditRecord;

class EditMcpTools extends EditRecord
{
    protected static string $resource = McpToolResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
