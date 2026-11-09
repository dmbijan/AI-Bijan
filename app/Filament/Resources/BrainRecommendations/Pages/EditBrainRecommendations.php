<?php

namespace App\Filament\Resources\BrainRecommendations\Pages;

use App\Filament\Resources\BrainRecommendations\RecommendationResource;
use Filament\Resources\Pages\EditRecord;

class EditBrainRecommendations extends EditRecord
{
    protected static string $resource = RecommendationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
