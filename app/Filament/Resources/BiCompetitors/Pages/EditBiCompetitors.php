<?php

namespace App\Filament\Resources\BiCompetitors\Pages;

use App\Filament\Resources\BiCompetitors\BiCompetitorResource;
use Filament\Resources\Pages\EditRecord;

class EditBiCompetitors extends EditRecord
{
    protected static string $resource = BiCompetitorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}