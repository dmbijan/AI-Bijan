<?php

namespace App\Filament\Resources\AiAgents2\Pages;

use App\Filament\Resources\AiAgents2\AiAgentResource;
use Filament\Resources\Pages\ListRecords;

class ListAiAgents2 extends ListRecords
{
    protected static string $resource = AiAgentResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}