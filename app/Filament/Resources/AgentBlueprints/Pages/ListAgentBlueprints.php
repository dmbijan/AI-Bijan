<?php

namespace App\Filament\Resources\AgentBlueprints\Pages;

use App\Filament\Resources\AgentBlueprints\AgentBlueprintResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgentBlueprints extends ListRecords
{
    protected static string $resource = AgentBlueprintResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
