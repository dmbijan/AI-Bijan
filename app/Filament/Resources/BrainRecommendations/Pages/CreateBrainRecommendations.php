<?php

namespace App\Filament\Resources\BrainRecommendations\Pages;

use App\Filament\Resources\BrainRecommendations\RecommendationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrainRecommendations extends CreateRecord
{
    protected static string $resource = RecommendationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
