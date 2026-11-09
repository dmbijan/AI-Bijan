<?php

namespace App\Filament\Resources\SignTemplates\Pages;

use App\Filament\Resources\SignTemplates\TemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSignTemplates extends ListRecords
{
    protected static string $resource = TemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
