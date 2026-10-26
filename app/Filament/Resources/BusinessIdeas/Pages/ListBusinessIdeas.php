<?php

namespace App\Filament\Resources\BusinessIdeas\Pages;

use App\Filament\Resources\BusinessIdeas\BusinessIdeaResource;
use Filament\Resources\Pages\ListRecords;

class ListBusinessIdeas extends ListRecords
{
    protected static string $resource = BusinessIdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}