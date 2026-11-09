<?php

namespace App\Filament\Resources\ProjectPortfolios\Pages;

use App\Filament\Resources\ProjectPortfolios\ProjectPortfolioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProjectPortfolios extends CreateRecord
{
    protected static string $resource = ProjectPortfolioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
