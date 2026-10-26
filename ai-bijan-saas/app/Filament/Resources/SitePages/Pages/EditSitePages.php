<?php

namespace App\Filament\Resources\SitePages\Pages;

use App\Filament\Resources\SitePages\SitePageResource;
use Filament\Resources\Pages\EditRecord;

class EditSitePages extends EditRecord
{
    protected static string $resource = SitePageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}