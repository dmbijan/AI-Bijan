<?php

namespace App\Filament\Resources\McpTools\Pages;

use App\Filament\Resources\McpTools\McpToolResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMcpTools extends ListRecords
{
    protected static string $resource = McpToolResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
