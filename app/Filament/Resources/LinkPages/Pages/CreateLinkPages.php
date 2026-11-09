<?php

namespace App\Filament\Resources\LinkPages\Pages;

use App\Filament\Resources\LinkPages\LinkPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLinkPages extends CreateRecord
{
    protected static string $resource = LinkPageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
