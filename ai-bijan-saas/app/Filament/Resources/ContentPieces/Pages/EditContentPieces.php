<?php

namespace App\Filament\Resources\ContentPieces\Pages;

use App\Filament\Resources\ContentPieces\ContentPieceResource;
use Filament\Resources\Pages\EditRecord;

class EditContentPieces extends EditRecord
{
    protected static string $resource = ContentPieceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}