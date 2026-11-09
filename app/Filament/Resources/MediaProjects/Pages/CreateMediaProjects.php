<?php

namespace App\Filament\Resources\MediaProjects\Pages;

use App\Filament\Resources\MediaProjects\MediaProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaProjects extends CreateRecord
{
    protected static string $resource = MediaProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
