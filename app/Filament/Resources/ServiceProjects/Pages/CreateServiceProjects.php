<?php

namespace App\Filament\Resources\ServiceProjects\Pages;

use App\Filament\Resources\ServiceProjects\ServiceProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceProjects extends CreateRecord
{
    protected static string $resource = ServiceProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}