<?php

namespace App\Filament\Resources\WorkflowTemplates\Pages;

use App\Filament\Resources\WorkflowTemplates\WorkflowTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkflowTemplates extends CreateRecord
{
    protected static string $resource = WorkflowTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
