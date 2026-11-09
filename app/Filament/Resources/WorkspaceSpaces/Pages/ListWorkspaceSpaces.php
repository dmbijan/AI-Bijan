<?php

namespace App\Filament\Resources\WorkspaceSpaces\Pages;

use App\Filament\Resources\WorkspaceSpaces\WorkspaceSpaceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkspaceSpaces extends ListRecords
{
    protected static string $resource = WorkspaceSpaceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
