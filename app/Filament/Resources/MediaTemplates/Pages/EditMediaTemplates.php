<?php

namespace App\Filament\Resources\MediaTemplates\Pages;

use App\Filament\Resources\MediaTemplates\MediaTemplateResource;
use Filament\Resources\Pages\EditRecord;

class EditMediaTemplates extends EditRecord
{
    protected static string $resource = MediaTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
