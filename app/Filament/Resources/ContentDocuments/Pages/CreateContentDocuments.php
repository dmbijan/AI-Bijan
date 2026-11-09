<?php

namespace App\Filament\Resources\ContentDocuments\Pages;

use App\Filament\Resources\ContentDocuments\ContentDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContentDocuments extends CreateRecord
{
    protected static string $resource = ContentDocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
