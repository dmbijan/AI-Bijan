<?php

namespace App\Filament\Resources\WorkspacePages\Pages;

use App\Filament\Resources\WorkspacePages\WorkspacePageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkspacePages extends ListRecords
{
    protected static string $resource = WorkspacePageResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
