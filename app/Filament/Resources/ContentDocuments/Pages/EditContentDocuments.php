<?php

namespace App\Filament\Resources\ContentDocuments\Pages;

use App\Filament\Resources\ContentDocuments\ContentDocumentResource;
use Filament\Resources\Pages\EditRecord;

class EditContentDocuments extends EditRecord
{
    protected static string $resource = ContentDocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
