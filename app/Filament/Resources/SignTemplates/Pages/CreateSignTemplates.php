<?php

namespace App\Filament\Resources\SignTemplates\Pages;

use App\Filament\Resources\SignTemplates\TemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSignTemplates extends CreateRecord
{
    protected static string $resource = TemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
