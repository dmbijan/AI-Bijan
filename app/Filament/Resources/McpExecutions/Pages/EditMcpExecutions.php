<?php

namespace App\Filament\Resources\McpExecutions\Pages;

use App\Filament\Resources\McpExecutions\McpExecutionResource;
use Filament\Resources\Pages\EditRecord;

class EditMcpExecutions extends EditRecord
{
    protected static string $resource = McpExecutionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
