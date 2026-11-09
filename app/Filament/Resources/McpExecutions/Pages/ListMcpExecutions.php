<?php

namespace App\Filament\Resources\McpExecutions\Pages;

use App\Filament\Resources\McpExecutions\McpExecutionResource;
use Filament\Resources\Pages\ListRecords;

class ListMcpExecutions extends ListRecords
{
    protected static string $resource = McpExecutionResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
