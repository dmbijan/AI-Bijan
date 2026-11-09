<?php

namespace App\Filament\Resources\WorkspaceSpaces\Pages;

use App\Filament\Resources\WorkspaceSpaces\WorkspaceSpaceResource;
use Filament\Resources\Pages\EditRecord;

class EditWorkspaceSpaces extends EditRecord
{
    protected static string $resource = WorkspaceSpaceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
