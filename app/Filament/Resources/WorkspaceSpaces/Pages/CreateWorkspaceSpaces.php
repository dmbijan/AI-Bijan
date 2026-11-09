<?php

namespace App\Filament\Resources\WorkspaceSpaces\Pages;

use App\Filament\Resources\WorkspaceSpaces\WorkspaceSpaceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkspaceSpaces extends CreateRecord
{
    protected static string $resource = WorkspaceSpaceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
