<?php

namespace App\Filament\Resources\SeoKeywords\Pages;

use App\Filament\Resources\SeoKeywords\SeoKeywordResource;
use Filament\Resources\Pages\ListRecords;

class ListSeoKeywords extends ListRecords
{
    protected static string $resource = SeoKeywordResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}