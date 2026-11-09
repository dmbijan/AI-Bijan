<?php

namespace App\Filament\Resources\McpResources\Pages;

use App\Filament\Resources\McpResources\McpResourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMcpResources extends ListRecords
{
    protected static string $resource = McpResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
