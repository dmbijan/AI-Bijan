<?php

namespace App\Filament\Resources\SitePages\Pages;

use App\Filament\Resources\SitePages\SitePageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSitePages extends CreateRecord
{
    protected static string $resource = SitePageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}