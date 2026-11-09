<?php

namespace App\Filament\Resources\SiteForms\Pages;

use App\Filament\Resources\SiteForms\SiteFormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteForms extends ListRecords
{
    protected static string $resource = SiteFormResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
