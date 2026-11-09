<?php

namespace App\Filament\Resources\SeoKeywordResearch\Pages;

use App\Filament\Resources\SeoKeywordResearch\SeoKeywordResearchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeoKeywordResearch extends ListRecords
{
    protected static string $resource = SeoKeywordResearchResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
