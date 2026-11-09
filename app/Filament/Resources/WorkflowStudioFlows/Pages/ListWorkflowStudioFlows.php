<?php

namespace App\Filament\Resources\WorkflowStudioFlows\Pages;

use App\Filament\Resources\WorkflowStudioFlows\WorkflowStudioFlowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowStudioFlows extends ListRecords
{
    protected static string $resource = WorkflowStudioFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
