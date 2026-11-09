<?php

namespace App\Filament\Resources\ProjectPortfolios\Pages;

use App\Filament\Resources\ProjectPortfolios\ProjectPortfolioResource;
use Filament\Resources\Pages\EditRecord;

class EditProjectPortfolios extends EditRecord
{
    protected static string $resource = ProjectPortfolioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
