<?php

namespace App\Filament\Resources\SeoKeywords\Pages;

use App\Filament\Resources\SeoKeywords\SeoKeywordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSeoKeywords extends CreateRecord
{
    protected static string $resource = SeoKeywordResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}