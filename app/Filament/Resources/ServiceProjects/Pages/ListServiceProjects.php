<?php

namespace App\Filament\Resources\ServiceProjects\Pages;

use App\Filament\Resources\ServiceProjects\ServiceProjectResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceProjects extends ListRecords
{
    protected static string $resource = ServiceProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}