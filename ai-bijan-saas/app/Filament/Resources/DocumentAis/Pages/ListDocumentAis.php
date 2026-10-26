<?php

namespace App\Filament\Resources\DocumentAis\Pages;

use App\Filament\Resources\DocumentAis\DocumentAiResource;
use Filament\Resources\Pages\ListRecords;

class ListDocumentAis extends ListRecords
{
    protected static string $resource = DocumentAiResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}