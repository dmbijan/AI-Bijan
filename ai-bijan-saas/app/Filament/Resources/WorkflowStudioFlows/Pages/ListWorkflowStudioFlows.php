<?php

namespace App\Filament\Resources\WorkflowStudioFlows\Pages;

use App\Filament\Resources\WorkflowStudioFlows\WorkflowStudioFlowResource;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowStudioFlows extends ListRecords
{
    protected static string $resource = WorkflowStudioFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}