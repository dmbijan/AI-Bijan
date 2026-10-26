<?php

namespace App\Filament\Resources\SignDocuments\Pages;

use App\Filament\Resources\SignDocuments\SignDocumentResource;
use Filament\Resources\Pages\ListRecords;

class ListSignDocuments extends ListRecords
{
    protected static string $resource = SignDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}