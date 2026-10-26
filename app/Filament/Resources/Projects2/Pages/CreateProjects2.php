<?php

namespace App\Filament\Resources\Projects2\Pages;

use App\Filament\Resources\Projects2\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProjects2 extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}