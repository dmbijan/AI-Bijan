<?php

namespace App\Filament\Resources\AdeReviews\Pages;

use App\Filament\Resources\AdeReviews\AdeReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListAdeReviews extends ListRecords
{
    protected static string $resource = AdeReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
