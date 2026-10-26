<?php

namespace App\Filament\Resources\WorkflowStudioFlows\Pages;

use App\Filament\Resources\WorkflowStudioFlows\WorkflowStudioFlowResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkflowStudioFlows extends CreateRecord
{
    protected static string $resource = WorkflowStudioFlowResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}