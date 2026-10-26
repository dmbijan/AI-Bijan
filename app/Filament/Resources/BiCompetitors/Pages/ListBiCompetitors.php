<?php

namespace App\Filament\Resources\BiCompetitors\Pages;

use App\Filament\Resources\BiCompetitors\BiCompetitorResource;
use Filament\Resources\Pages\ListRecords;

class ListBiCompetitors extends ListRecords
{
    protected static string $resource = BiCompetitorResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}