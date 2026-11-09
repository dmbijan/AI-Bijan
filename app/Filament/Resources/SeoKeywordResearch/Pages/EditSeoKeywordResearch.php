<?php

namespace App\Filament\Resources\SeoKeywordResearch\Pages;

use App\Filament\Resources\SeoKeywordResearch\SeoKeywordResearchResource;
use Filament\Resources\Pages\EditRecord;

class EditSeoKeywordResearch extends EditRecord
{
    protected static string $resource = SeoKeywordResearchResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
