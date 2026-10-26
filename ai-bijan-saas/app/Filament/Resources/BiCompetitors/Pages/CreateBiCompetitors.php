<?php

namespace App\Filament\Resources\BiCompetitors\Pages;

use App\Filament\Resources\BiCompetitors\BiCompetitorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBiCompetitors extends CreateRecord
{
    protected static string $resource = BiCompetitorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}