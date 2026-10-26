<?php

namespace App\Filament\Resources\Projects2\Pages;

use App\Filament\Resources\Projects2\ProjectResource;
use Filament\Resources\Pages\EditRecord;

class EditProjects2 extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}