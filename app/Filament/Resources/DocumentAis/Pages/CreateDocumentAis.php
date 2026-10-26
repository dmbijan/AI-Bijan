<?php

namespace App\Filament\Resources\DocumentAis\Pages;

use App\Filament\Resources\DocumentAis\DocumentAiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentAis extends CreateRecord
{
    protected static string $resource = DocumentAiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}