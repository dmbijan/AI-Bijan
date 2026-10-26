<?php

namespace App\Filament\Resources\SignDocuments\Pages;

use App\Filament\Resources\SignDocuments\SignDocumentResource;
use Filament\Resources\Pages\EditRecord;

class EditSignDocuments extends EditRecord
{
    protected static string $resource = SignDocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}