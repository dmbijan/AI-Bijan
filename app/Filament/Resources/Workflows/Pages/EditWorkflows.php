<?php

namespace App\Filament\Resources\Workflows\Pages;

use App\Filament\Resources\Workflows\WorkflowResource;
use Filament\Resources\Pages\EditRecord;

class EditWorkflows extends EditRecord
{
    protected static string $resource = WorkflowResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
