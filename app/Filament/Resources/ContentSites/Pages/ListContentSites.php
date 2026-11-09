<?php

namespace App\Filament\Resources\ContentSites\Pages;

use App\Filament\Resources\ContentSites\ContentSiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentSites extends ListRecords
{
    protected static string $resource = ContentSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
