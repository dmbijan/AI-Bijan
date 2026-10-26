<?php

namespace App\Filament\Resources\DocumentAis\Pages;

use App\Filament\Resources\DocumentAis\DocumentAiResource;
use Filament\Resources\Pages\EditRecord;

class EditDocumentAis extends EditRecord
{
    protected static string $resource = DocumentAiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}