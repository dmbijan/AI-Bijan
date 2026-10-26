<?php

namespace App\Filament\Resources\Projects2\Pages;

use App\Filament\Resources\Projects2\ProjectResource;
use Filament\Resources\Pages\ListRecords;

class ListProjects2 extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}