<?php

namespace App\Filament\Resources\SiteForms\Pages;

use App\Filament\Resources\SiteForms\SiteFormResource;
use Filament\Resources\Pages\EditRecord;

class EditSiteForms extends EditRecord
{
    protected static string $resource = SiteFormResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}