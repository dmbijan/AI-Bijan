<?php

namespace App\Filament\Resources\SeoKeywords\Pages;

use App\Filament\Resources\SeoKeywords\SeoKeywordResource;
use Filament\Resources\Pages\EditRecord;

class EditSeoKeywords extends EditRecord
{
    protected static string $resource = SeoKeywordResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}