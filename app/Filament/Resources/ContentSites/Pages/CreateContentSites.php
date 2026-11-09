<?php

namespace App\Filament\Resources\ContentSites\Pages;

use App\Filament\Resources\ContentSites\ContentSiteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContentSites extends CreateRecord
{
    protected static string $resource = ContentSiteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
