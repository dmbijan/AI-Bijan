<?php

namespace App\Filament\Resources\BusinessIdeas\Pages;

use App\Filament\Resources\BusinessIdeas\BusinessIdeaResource;
use Filament\Resources\Pages\EditRecord;

class EditBusinessIdeas extends EditRecord
{
    protected static string $resource = BusinessIdeaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}