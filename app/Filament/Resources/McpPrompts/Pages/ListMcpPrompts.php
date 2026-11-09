<?php

namespace App\Filament\Resources\McpPrompts\Pages;

use App\Filament\Resources\McpPrompts\McpPromptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMcpPrompts extends ListRecords
{
    protected static string $resource = McpPromptResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
