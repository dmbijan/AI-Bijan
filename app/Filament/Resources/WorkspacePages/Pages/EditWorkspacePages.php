<?php

namespace App\Filament\Resources\WorkspacePages\Pages;

use App\Filament\Resources\WorkspacePages\WorkspacePageResource;
use Filament\Resources\Pages\EditRecord;

class EditWorkspacePages extends EditRecord
{
    protected static string $resource = WorkspacePageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
