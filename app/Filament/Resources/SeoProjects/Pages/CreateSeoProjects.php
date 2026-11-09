<?php

namespace App\Filament\Resources\SeoProjects\Pages;

use App\Filament\Resources\SeoProjects\SeoProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSeoProjects extends CreateRecord
{
    protected static string $resource = SeoProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
