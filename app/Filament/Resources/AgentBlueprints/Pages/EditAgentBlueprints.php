<?php

namespace App\Filament\Resources\AgentBlueprints\Pages;

use App\Filament\Resources\AgentBlueprints\AgentBlueprintResource;
use Filament\Resources\Pages\EditRecord;

class EditAgentBlueprints extends EditRecord
{
    protected static string $resource = AgentBlueprintResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}