<?php

namespace App\Filament\Resources\BiIdeas\Pages;

use App\Filament\Resources\BiIdeas\BiIdeaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBiIdeas extends ListRecords
{
    protected static string $resource = BiIdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
