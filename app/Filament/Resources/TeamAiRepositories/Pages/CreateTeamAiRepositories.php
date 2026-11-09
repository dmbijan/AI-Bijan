<?php

namespace App\Filament\Resources\TeamAiRepositories\Pages;

use App\Filament\Resources\TeamAiRepositories\TeamAiRepositoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeamAiRepositories extends CreateRecord
{
    protected static string $resource = TeamAiRepositoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
