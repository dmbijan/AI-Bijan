<?php

namespace App\Filament\Resources\TeamAiRepositories\Pages;

use App\Filament\Resources\TeamAiRepositories\TeamAiRepositoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeamAiRepositories extends ListRecords
{
    protected static string $resource = TeamAiRepositoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
