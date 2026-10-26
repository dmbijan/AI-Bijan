<?php

namespace App\Filament\Resources\ContentIdeas\Pages;

use App\Filament\Resources\ContentIdeas\ContentIdeaResource;
use Filament\Resources\Pages\EditRecord;

class EditContentIdeas extends EditRecord
{
    protected static string $resource = ContentIdeaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}