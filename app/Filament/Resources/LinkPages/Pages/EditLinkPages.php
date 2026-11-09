<?php

namespace App\Filament\Resources\LinkPages\Pages;

use App\Filament\Resources\LinkPages\LinkPageResource;
use Filament\Resources\Pages\EditRecord;

class EditLinkPages extends EditRecord
{
    protected static string $resource = LinkPageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
