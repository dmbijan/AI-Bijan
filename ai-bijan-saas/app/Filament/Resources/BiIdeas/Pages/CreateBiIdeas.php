<?php

namespace App\Filament\Resources\BiIdeas\Pages;

use App\Filament\Resources\BiIdeas\BiIdeaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBiIdeas extends CreateRecord
{
    protected static string $resource = BiIdeaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}