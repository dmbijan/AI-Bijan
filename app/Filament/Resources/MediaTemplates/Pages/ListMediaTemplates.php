<?php

namespace App\Filament\Resources\MediaTemplates\Pages;

use App\Filament\Resources\MediaTemplates\MediaTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMediaTemplates extends ListRecords
{
    protected static string $resource = MediaTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
