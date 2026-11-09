<?php

namespace App\Filament\Resources\ProjectPortfolios\Pages;

use App\Filament\Resources\ProjectPortfolios\ProjectPortfolioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjectPortfolios extends ListRecords
{
    protected static string $resource = ProjectPortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
