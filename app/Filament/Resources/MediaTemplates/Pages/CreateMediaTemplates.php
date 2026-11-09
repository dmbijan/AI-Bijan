<?php

namespace App\Filament\Resources\MediaTemplates\Pages;

use App\Filament\Resources\MediaTemplates\MediaTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaTemplates extends CreateRecord
{
    protected static string $resource = MediaTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
