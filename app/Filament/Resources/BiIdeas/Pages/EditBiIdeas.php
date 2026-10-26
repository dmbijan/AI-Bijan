<?php

namespace App\Filament\Resources\BiIdeas\Pages;

use App\Filament\Resources\BiIdeas\BiIdeaResource;
use Filament\Resources\Pages\EditRecord;

class EditBiIdeas extends EditRecord
{
    protected static string $resource = BiIdeaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}