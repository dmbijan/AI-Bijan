<?php

namespace App\Filament\Resources\FlowDefinitions\Pages;

use App\Filament\Resources\FlowDefinitions\FlowDefinitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFlowDefinitions extends ListRecords
{
    protected static string $resource = FlowDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
