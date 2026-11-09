<?php

namespace App\Filament\Resources\ContentDocuments\Pages;

use App\Filament\Resources\ContentDocuments\ContentDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentDocuments extends ListRecords
{
    protected static string $resource = ContentDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
