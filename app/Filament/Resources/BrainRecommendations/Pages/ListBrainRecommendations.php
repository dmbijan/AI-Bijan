<?php

namespace App\Filament\Resources\BrainRecommendations\Pages;

use App\Filament\Resources\BrainRecommendations\RecommendationResource;
use Filament\Resources\Pages\ListRecords;

class ListBrainRecommendations extends ListRecords
{
    protected static string $resource = RecommendationResource::class;

    protected function getHeaderActions(): array
    {
        // Tiada "Cipta" — rekod ini dikawal oleh servis domain.
        return [];
    }
}
